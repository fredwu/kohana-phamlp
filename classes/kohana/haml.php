<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana Haml bridge for PHamlP
 *
 * @package     PHamlP
 * @subpackage  Haml
 * @author      Fred Wu <fred@wuit.com>
 * @copyright   Wuit.com <http://wuit.com/>
 * @license     http://www.opensource.org/licenses/mit-license.php
 */
class Kohana_Haml {
	
	/**
	 * @var  array  Kohana::config('phamlp')
	 */
	protected static $config;
	
	/**
	 * Returns a new View object
	 *
	 * @see     View::factory()
	 * @param   string  view filename
	 * @param   array   array of values
	 * @return  View
	 */
	public static function factory($file = NULL, array $data = NULL)
	{
		self::include_phamlp();
		
		$haml_file = self::compile_haml($file, $data);
		
		return new View($haml_file, $data);
	}
	
	/**
	 * Includes the necessary phamlp parser file
	 *
	 * @return void
	 */
	private static function include_phamlp()
	{
		while ( ! class_exists('HamlParser'))
		{
			self::$config = Kohana::config('phamlp');
			$haml_dir     = self::$config['phamlp']['lib_dir'].'haml';
			
			if ( ! is_dir($haml_dir))
			{
				throw new Exception("Cannot find phamlp's HAML directory.");
			}
			
			set_include_path(get_include_path().PATH_SEPARATOR.$haml_dir);
			
			require_once 'HamlParser.php';
		}
	}
	
	/**
	 * Compiles the HAML template from the given HTML/PHP template
	 *
	 * @param   string  view filename
	 * @param   array   array of values
	 * @return  string  path of the compiled HAML file
	 */
	private static function compile_haml($file, $data)
	{
		$view_dir       = APPPATH.'views/';
		$cache_dir      = self::$config['phamlp']['haml']['cache_dir'];
		$cache_root     = $view_dir.$cache_dir;
		$cache_dir_real = $cache_root.dirname($file);
		$haml_ext       = self::$config['phamlp']['haml']['extension'];
		$cached_file    = $view_dir.$cache_dir.$file.EXT;
		
		self::make_dir_writable($view_dir);
		self::create_dir_unless_exists($cache_root);
		
		// in development mode, let's reload the template on each request
		if (Kohana::$environment === Kohana::DEVELOPMENT)
		{
			self::remove_haml_file($cached_file);
		}
		
		if ( ! is_file($cached_file))
		{
			$haml = new HamlParser(array());
			$haml->parse($view_dir.$file.$haml_ext, $cache_dir_real);
		}
		
		return $cache_dir.$file;
	}
	
	/**
	 * Checks and makes the directory writable
	 *
	 * @param   string  $dir  path of the directory
	 * @return  void
	 */
	protected static function make_dir_writable($dir)
	{
		if ( ! is_writable($dir))
		{
			chmod($dir, 0777);
		}
	}
	
	/**
	 * Creates the directory unless it already exists
	 *
	 * @param   string  $dir  path of the directory
	 * @return  void
	 */
	protected static function create_dir_unless_exists($dir)
	{
		if ( ! is_dir($dir))
		{
			mkdir($dir);
		}
	}
	
	protected static function remove_haml_file($file)
	{
		@unlink($file);
	}
}