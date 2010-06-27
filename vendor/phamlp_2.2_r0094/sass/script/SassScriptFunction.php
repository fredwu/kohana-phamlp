<?php
/* SVN FILE: $Id: SassScriptFunction.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * SassScriptFunction class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.script
 */

require_once('SassScriptFunctions.php');

/**
 * SassScriptFunction class.
 * Preforms a SassScript function.
 * @package			PHamlP
 * @subpackage	Sass.script
 */
class SassScriptFunction {
	/**@#+
	 * Regexes for matching and extracting colours
	 */
	const MATCH = '/^(\w+)\((.+?)\)/';
	const NAME = 1;
	const ARGUMENTS = 2;

	private $_name;
	private $_args;

	/**
	 * SassScriptFunction constructor
	 * @param string value of the Function type
	 * @return SassScriptFunction
	 */
	public function __construct($value) {
	  preg_match(self::MATCH, $value, $matches);
	  $this->_name = $matches[self::NAME];
	  $args = explode(',', $matches[self::ARGUMENTS]);
	  foreach ($args as $arg) {
	  	$this->_args[] = trim($arg);
	  } // foreach
	}

	/**
	 * Evaluates the function.
	 * @return Function the value of this Function
	 */
	public function perform() {
		if (method_exists('SassScriptFunctions', $this->_name)) {
			return call_user_func_array(array('SassScriptFunctions', $this->_name),
				$this->_args);
		}
		else {
			return new SassString($this->_name . '(' . join(', ', $this->_args) . ')');
		}
	}

	/**
	 * Returns a value indicating if a token of this type can be matched at
	 * the start of the subject string.
	 * @param string the subject string
	 * @return mixed match at the start of the string or false if no match
	 */
	static public function isa($subject) {
		return (preg_match(self::MATCH, $subject, $matches) ? $matches[0] : false);
	}
}
