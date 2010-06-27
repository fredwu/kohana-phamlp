<?php
/* SVN FILE: $Id: SassMixinDefinitionNode.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * SassMixinDefinitionNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

/**
 * SassMixinDefinitionNode class.
 * Represents a Mixin definition.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassMixinDefinitionNode extends SassNode {
	const IDENTIFIER = '=';
	const MATCH = '/^=([-\w]+)(?:\((.+?)\))?$/';
	const NAME = 1;
	const ARGUMENTS = 2;

	/**
	 * @var string name of the mixin
	 */
	private $name;
	/**
	 * @var array arguments for the mixin
	 */
	private $args = array();
	/**
	 * @var array default values for arguments
	 */
	private $defaultValues;

	/**
	 * SassMixinDefinitionNode constructor.
	 * @param string name of the mixin
	 * @param string arguments for the mixin
	 * @return SassMixinDefinitionNode
	 */
	public function __construct($name, $args = '') {
	  $this->name = $name;
	  if (!empty($args)) {
		  $_args = explode(',', $args);
		  $required = true;
		  foreach ($_args as $arg) {
	  		$arg = explode('=', trim($arg));
	  		$name = substr(trim($arg[0]), 1);
	  		$this->args[] = $name;
	  		if (count($arg) == 2) {
					$required = false;
	  			$this->defaultValues[$name] = trim($arg[1]);
	  		}
	  		elseif (!$required) {
					throw new SassMixinDefinitionNodeException("Mixin::{$this->name}: Required variables must be defined before optional variables");
	  		}
		  } // foreach
	  }
	}

	/**
	 * Parse this node.
	 * Add this mixin to  the current context.
	 * @param SassContext the context in which this node is parsed
	 * @return array the parsed node - an empty array
	 */
	public function parse($context) {
		$context->addMixin($this->name, $this);
		return array();
	}

	public function getArgs() {
	  return $this->args;
	}

	public function hasDefault($name) {
	  return isset($this->defaultValues[$name]);
	}

	public function getDefault($name) {
	  return $this->defaultValues[$name];
	}

	/**
	 * Returns a value indicating if the line represents this type of node.
	 * @param array the line to test
	 * @return boolean true if the line represents this type of node, false if not
	 */
	static public function isa($line) {
		return $line['source'][0] === self::IDENTIFIER;
	}

	/**
	 * Returns the matches for this type of node.
	 * @param array the line to match
	 * @return array matches
	 */
	static public function match($line) {
		preg_match(self::MATCH, $line['source'], $matches);
		return $matches;
	}
}
