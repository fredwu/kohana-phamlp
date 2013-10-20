<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana Controller that utilises Haml template
 *
 * @package     PHamlP
 * @subpackage  Controller
 * @author      Fred Wu <fred@wuit.com>
 * @copyright   Wuit.com <http://wuit.com/>
 * @license     http://www.opensource.org/licenses/mit-license.php
 */
class Controller_Haml_Template extends Controller_haml {
	
	/**
	 * @var  string  page template
	 */
	public $template = 'layouts/application';
	
	/**
	 * @var  array  view data
	 */
	public $view_data = array();
	
	/**
	 * @see  http://code.google.com/p/phamlp/wiki/HamlOptions
	 * @var  array  haml options
	 */
	public $haml_options = array();
	
	/**
	 * @var  boolean  auto render template
	 **/
	public $auto_render = TRUE;
	
	/**
	 * Loads the template [View] object.
	 */
	public function before()
	{
		if ($this->auto_render === TRUE)
		{
			$this->template = Haml::factory($this->template, null, $this->haml_options);
		}
		
		return parent::before();
	}

	public function __set($name, $value)
	{
		$this->view_data[$name] = $value;
	}

	/**
	 * Assigns the template [View] as the request response.
	 */
	public function after()
	{
		if ($this->auto_render === TRUE)
		{
			if(substr(Kohana::VERSION, 0, 3) == '3.0')
			{
				$this->request->response = $this->template;
			}
			else
			{
				$this->response->body($this->template);
			}
		}
		
		return parent::after();
	}
}
