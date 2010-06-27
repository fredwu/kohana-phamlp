<?php
/* SVN FILE: $Id: SassMixinNode.php 76 2010-05-03 11:32:32Z chris.l.yates $ */
/**
 * SassMixinNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

/**
 * SassMixinNode class.
 * Represents a Mixin.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassMixinNode extends SassNode {
	const IDENTIFIER = '+';
	const MATCH = '/^\+([-\w]+)(?:\((.*?)\))?$/';
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
	 * SassMixinDefinitionNode constructor.
	 * @param string name of the mixin
	 * @param string arguments for the mixin
	 * @return SassMixinNode
	 */
	public function __construct($name, $args = '') {
	  $this->name = $name;
	  if (!empty($args)) {
		  $_args = explode(',', $args);
		  foreach ($_args as $arg) {
	  		$this->args[] = trim($arg);
		  } // foreach
	  }
	}

	/**
	 * Parse this node.
	 * Set any attributes passed and any optional arguments not passed to their
	 * defaults, then render the children of the mixin definition.
	 * @param SassContext the context in which this node is parsed
	 * @return array the parsed node
	 */
	public function parse($context) {
		$mixin = $context->getMixin($this->name);

		$context = new SassContext($context);
		foreach ($mixin->args as $n => $name) {
			if (isset($this->args[$n])) {
				$context->setVariable($name, $this->evaluate($this->args[$n], $context));
			}
			elseif ($mixin->hasDefault($name)) {
				$context->setVariable($name, $this->evaluate($mixin->getDefault($name), $context));
			}
			else {
				throw new SassMixinNodeException("Required variable - $name - not given when using Mixin::{$this->name}\nMixin used at Line {$this->line['number']}: " . (is_array($this->line['file']) ? join(DIRECTORY_SEPARATOR, $this->line['file']) : '')."\nMixin defined at Line {$mixin->line['number']}: " . (is_array($mixin->line['file']) ? join(DIRECTORY_SEPARATOR, $mixin->line['file']) : ''));
			}
		} // foreach

		$children = array();
		foreach ($mixin->children as $child) {
			$child->parent = $this;
			$children = array_merge($children, $child->parse($context));
		} // foreach

		$context->merge();
		return $children;
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
