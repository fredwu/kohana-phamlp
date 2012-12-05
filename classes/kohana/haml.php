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
class Kohana_Haml extends View {

	/**
	 * @var  array  Kohana::config('phamlp')
	 */
	protected static $config;

	protected $data;
	protected $options;


	/**
	 * Prepares Haml view
	 *
	 * @see     View::__construct()
	 * @param   string  view filename
	 * @param   array   array of values
	 * @param   array   options
	 * @return  View
	 */
	 public function __construct($file = NULL, array $data = NULL, array $options = array())
	 {
        self::$config = self::$config ?: Kohana::$config->load('phamlp');
        $this->data = $data;
 		$this->options = $options;
        $this->compile_haml($file);
		return parent::__construct($file, $this->data);
	 }

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
		return new Haml($file, $data, $options);
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


    private static function haml_ext()
    {
        return self::$config['haml']['extension'];
    }

	/**
	 * Compiles the HAML template from the given HTML/PHP template
	 *
	 * @param   string  view filename
	 * @param   array   array of values
	 * @param   array   options
	 * @return  string  path of the compiled HAML file
	 */
	private function compile_haml($file)
	{
		$cache_dir      = self::$config['haml']['cache_dir'].'/';
		$cache_root     = APPPATH.'cache/'.self::$config['haml']['cache_dir'].'/';

		$cache_dir_real = $cache_root.dirname($file);
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
			$options = array_merge(self::$config['haml']['options'], $this->options);

			$haml = new HamlParser($options);
			$haml->parse(
                Kohana::find_file('views', $file, self::haml_ext()),
                $cache_dir_real
            );
		}

		return $file;
	}

	/**
	 * Kohana 3.1 uses very "silly" extension checking
	 * We're overloading set_filename to seek our view in cache
	 *
	 * @param   string  $dir  path of the directory
	 * @return  void
	 */
	public function set_filename($file, $validate_file_change = true)
	{
		// Detect if there was a file extension
		$_file = explode('.', $file);

		// If there are several components
		if (count($_file) > 1)
		{
			// Take the extension
			$ext = array_pop($_file);
			$file = implode('.', $_file);
		}
		// Otherwise set the extension to the standard
		else
		{
			$ext = ltrim(EXT, '.');
		}

        $base_name = self::$config['haml']['cache_dir'].'/'.$file;
        $path = self::find_file('cache', $base_name, $ext);

        // izhevsky: added 2 fixes:
        // path exists, but cached file has to be recompiled, because source .haml was changed
        // path not exists, because previous view didn't exist, but now we set up right name

        $real_file = self::find_file('views', $file, self::haml_ext());
        if ($path === FALSE)
        {
            if (file_exists($real_file))
            {
                // file exists, recompile
                self::compile_haml($file);
                $this->set_filename($file, false);
                return;
            }
            throw new Kohana_View_Exception(
                'The requested view :file could not be found',
                array(':file' => $file.($ext ? '.haml' : ''))
            );
        }
        elseif (Kohana::$environment === Kohana::DEVELOPMENT &&
            $validate_file_change &&
            filemtime($real_file) >= filemtime($path)
        )
        {
            self::compile_haml($file);
            $this->set_filename($file, false);
            return;
        }

        // Store the file path locally
        $this->_file = $path;

        return $this;
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

    // platform-independent wrapper around Kohana::find_file
	protected static function find_file($dir, $file, $ext)
	{
        if(substr(Kohana::VERSION, 0, 3) == '3.0')
        {
            return Kohana::find_file($dir, $file);
        }
        return Kohana::find_file($dir, $file, $ext);
	}
}
