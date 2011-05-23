<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana Sass bridge for PHamlP
 *
 * @package     PHamlP
 * @subpackage  Sass
 * @author      Michał Matyas <michal@6irc.net>
 * @copyright   Nerdblog.pl <http://nerdblog.pl>
 * @license     http://www.opensource.org/licenses/mit-license.php
 */
class Kohana_Sass {
	
	/**
	 * @var  array  Kohana::config('phamlp')
	 */
	protected static $config,
	                 $cache_dir,
	                 $scss_dir,
	                 $css_dir;
	
	/**
	 * Returns a path to compiled SASS/SCSS file
	 *
	 * @param   string  view filename
	 * @return  string  cached filename
	 */
	public static function from($file, $options = array())
	{
		self::read_config();	
		return self::compile_sass_file($file, $options);
	}

	/**
	 * Returns directly a compiled SASS/SCSS file
	 *
	 * @param   string  view filename
	 * @return  string  CSS file
	 */
	public static function compile($file, $options = array())
	{
		self::read_config();
		return self::compile_sass($file, $options);
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
			// TODO: Clean rewrite
			self::$config      = Kohana::config('phamlp');
			self::$cache_dir   = APPPATH.'cache/'.self::$config['sass']['cache_dir'];
			self::$scss_dir    = APPPATH.'views/'.self::$config['sass']['scss_dir'];
			self::$css_dir     = DOCROOT.self::$config['sass']['css_dir'];
		}
	}
	
	/**
	 * Compiles the CSS style from the given SASS/CSS file
	 *
	 * @param   string  view filename
	 * @param   array   options
	 * @return  string  path of the compiled Sass file
	 */
	private static function compile_sass($file, $options = array())
	{	
		$options = array_merge(
		           self::$config['sass']['options'],
		            
		           array('cache_location'    => self::$cache_dir,
		                 'load_paths'        => self::$scss_dir,
		                 'template_location' => self::$scss_dir
		                ), 
		           $options);
			
		$sass = new SassParser($options);
		return $sass->toCss(self::$scss_dir . '/' . $file, TRUE);
	}

	private static function compile_sass_file($file, $options = array())
	{
		// SassParser.php mentions css_location config value, but it's not used anywhere
		// I guess it's just a stub, so I implement this here
		// TODO: Move creating a compiled CSS file directly into d4rky-pl/phamlp	

		self::check_directories();
		$compiled_css  = self::$config['sass']['css_dir'].'/'.pathinfo($file, PATHINFO_FILENAME).'.css';
		$compiled_path = self::$css_dir.'/'.pathinfo($file, PATHINFO_FILENAME).'.css';

		// in development mode, let's reload the template on each request
		if (Kohana::$environment === Kohana::DEVELOPMENT)
		{
			self::remove_sass_file($compiled_path);
		}
		
//		if ( ! is_file($compiled_css))
//		{
			file_put_contents($compiled_path, self::compile_sass($file, $options));
//		}

		return url::base().$compiled_css;
	}
	
	
	protected static function check_directories()
	{
		foreach(array(self::$cache_dir, self::$css_dir) as $dir)
		{
			self::create_dir_unless_exists($dir);
			self::make_dir_writable($dir);
		}
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
	
	protected static function remove_sass_file($file)
	{
		@unlink($file);
	}
}
