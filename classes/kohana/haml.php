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
	 * @param   array   options
	 * @return  View
	 */
	public static function factory($file = NULL, array $data = NULL, array $options = array())
	{
		self::read_config();
		
		$haml_file = self::compile_haml($file, $data, $options);
		
		return new View($haml_file, $data);
	}
	
	/**
	 * Sets a global variable, similar to [View::set], except that the
	 * variable will be accessible to all views.
	 *
	 * @see     View::set_global()
	 * @param   string  variable name or an array of variables
	 * @param   mixed   value
	 * @return  void
	 */
	public static function set_global($key, $value = NULL)
	{
		View::set_global($key, $value);
	}
	
	/**
	 * Assigns a global variable by reference, similar to [View::bind], except
	 * that the variable will be accessible to all views.
	 *
	 * @see     View::set_global()
	 * @param   string  variable name
	 * @param   mixed   referenced variable
	 * @return  void
	 */
	public static function bind_global($key, & $value)
	{
		View::bind_global($key, $value);
	}
	
	/**
	 * Includes the necessary phamlp parser file
	 *
	 * @return void
	 */
	private static function read_config()
	{
		if( !is_array(self::$config) )
		{
			return self::$config = Kohana::config('phamlp');			
		}
		else
		{
			return self::$config;
		}
	}
	
	/**
	 * Compiles the HAML template from the given HTML/PHP template
	 *
	 * @param   string  view filename
	 * @param   array   array of values
	 * @param   array   options
	 * @return  string  path of the compiled HAML file
	 */
	private static function compile_haml($file, $data, $options)
	{
		$view_dir       = APPPATH.'views/';

		// '../' part is necessary for Kohana's find_file to work properly
		$cache_dir      = "../cache/".self::$config['haml']['cache_dir'];

		$cache_root     = $view_dir.$cache_dir;
		$cache_dir_real = $cache_root.dirname($file);
		$haml_ext       = self::$config['haml']['extension'];
		$cached_file    = $cache_root.$file.EXT;
		
		self::create_dir_unless_exists($cache_root);
		self::make_dir_writable($cache_root);
		
		// in development mode, let's reload the template on each request
		if (Kohana::$environment === Kohana::DEVELOPMENT)
		{
			self::remove_haml_file($cached_file);
		}
		
		if ( ! is_file($cached_file))
		{
			self::create_dir_unless_exists($cache_root . dirname($file));
			$options = array_merge(self::$config['haml']['options'], $options);
			
			$haml = new HamlParser($options);
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
			mkdir($dir, 0777, TRUE);
		}
	}
	
	protected static function remove_haml_file($file)
	{
		@unlink($file);
	}
}
