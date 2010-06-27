<?php
/* SVN FILE: $Id: SassPropertyNode.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * SassPropertyNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

/**
 * SassPropertyNode class.
 * Represents a CSS property.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassPropertyNode extends SassNode {
	const MATCH_PROPERTY_NEW = '/^([^\s=:"]+)(?:\s*(=)|:)(?:\s+|$)(.*?)(;)?$/';
	const MATCH_PROPERTY_OLD = '/^:([^\s=:]+)(?:\s*(=)\s*|\s+|$)(.*)/';
	const NAME	 = 1;
	const SCRIPT = 2;
	const VALUE	 = 3;
	const IS_SCRIPT = '=';

	/**
	 * @var string property name
	 */
	private $name;
	/**
	 * @var string property value or expression to evaluate
	 */
	private $value;
	/**
	 * @var boolean whether the value is a SassScript expression
	 */
	private $isScript;

	/**
	 * SassPropertyNode constructor.
	 * @param string name of the property
	 * @param string value or expression for the property.
	 * Empty string designates this node as a property namespace.
	 * @param boolean whether the value is a SassScript expression
	 * @return SassPropertyNode
	 */
	public function __construct($name, $value, $isScript = false) {
		$this->name = $name;
		$this->value = $value;
		$this->isScript = $isScript;
	}

	/**
	 * Parse this node.
	 * If the node is a property namespace return all parsed child nodes. If not
	 * return the parsed version of this node.
	 * @param SassContext the context in which this node is parsed
	 * @return array the parsed node
	 */
	public function parse($context) {
		if ($this->isNamespace()) {
			$parsed = array();
			foreach ($this->children as $child) {
				$parsed = array_merge($parsed, $child->parse($context));
			} // foreach
			return $parsed;
		}
	  else {
	  	$node = clone $this;
			$node->name = ($this->inNamespace() ? "{$this->namespace}-" : '') .
				$this->name;
	  	$node->value = ($this->isScript ?
	  		$this->evaluate($this->interpolate($this->value, $context), $context) :
	  		$this->value);
			return array($node);
	  }
	}

	/**
	 * Render this node.
	 * @return string the rendered node
	 */
	public function render() {
		$this->indentLevel = $this->parent->indentLevel + 1;
		return $this->renderer->renderProperty($this);
	}

	/**
	 * Returns a value indicating if this node is a property namespace
	 * @return boolean true if this node is a property namespace, false if not
	 */
	protected function isNamespace() {
	  return $this->value === '';
	}

	/**
	 * Returns a value indicating if this node is in a namespace
	 * @return boolean true if this node is in a property namespace, false if not
	 */
	public function inNamespace() {
		$parent = $this->parent;
		do {
			if ($parent instanceof SassPropertyNode) {
				return true;
			}
			$parent = $parent->parent;
		} while (is_object($parent));
	  return false;
	}

	/**
	 * Returns the namespace for this node
	 * @return string the namespace for this node
	 */
	protected function getNamespace() {
		$namespace = array();
		$parent = $this->parent;
		do {
			if ($parent instanceof SassPropertyNode) {
				$namespace[] = $parent->name;
			}
			$parent = $parent->parent;
		} while (is_object($parent));
		return join('-', array_reverse($namespace));
	}

	/**
	 * Returns the name of this property.
	 * If the property is in a namespace the namespace is prepended
	 * @return string the name of this property
	 */
	public function getName() {
	  return $this->name;
	}

	/**
	 * Returns the parsed value of this property.
	 * @return string the parsed value of this property
	 */
	public function getValue() {
	  return $this->value;
	}

	/**
	 * Returns a value indicating if the line represents this type of node.
	 * @param array the line to test
	 * @param string the property syntax being used
	 * @return boolean true if the line represents this type of node, false if not
	 */
	static public function isa($line, $syntax) {
		$matches = self::match($line, $syntax);
		if (!empty($matches)) {
	  	if ($line['indentLevel'] === 0) {
	  		throw new SassPropertyNodeException("Illegal property assignement; properties can not be assigned at zero indent level\nLine {$line['number']}: " . (is_array($line['file']) ? join(DIRECTORY_SEPARATOR, $line['file']) : ''));
	  	}
	  	else {
				return true;
	  	}
		}
		else {
			return false;
		}
	}

	/**
	 * Returns the matches for this type of node.
	 * @param array the line to match
	 * @param string the property syntax being used
	 * @return array matches
	 */
	static public function match($line, $syntax) {
		switch ($syntax) {
			case 'new':
				$m = preg_match(self::MATCH_PROPERTY_NEW, $line['source'], $matches);
				break;
			case 'old':
				$m = preg_match(self::MATCH_PROPERTY_OLD, $line['source'], $matches);
				break;
			default:
				$m = preg_match(self::MATCH_PROPERTY_NEW, $line['source'], $matches);
				if ($m == 0) {
					$m = preg_match(self::MATCH_PROPERTY_OLD, $line['source'], $matches);
				}
				break;
		}
		return $matches;
	}
}
