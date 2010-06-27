<?php
/* SVN FILE: $Id: SassVariableNode.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * SassVariableNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

/**
 * SassVariableNode class.
 * Represents a variable.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassVariableNode extends SassNode {
	const MATCH = '/^!(.+?)\s*((?:\|\|)?=)\s?(.+)?$/';
	const NAME = 1;
	const ASSIGNMENT = 2;
	const VALUE = 3;
	const IDENTIFIER = '!';
	const IS_OPTIONAL = '||=';

	/**
	 * @var string name of the variable
	 */
	private $name;
	/**
	 * @var string value of the variable or expression to evaluate
	 */
	private $value;
	/**
	 * @var boolean whether the variable is optionally assigned
	 */
	private $isOptional;

	/**
	 * SassVariableNode constructor.
	 * @param string name of the variable
	 * @param string value of the variable or expression to evaluate
	 * @param boolean whether the variable is optionally assigned
	 * @return SassVariableNode
	 */
	public function __construct($name, $value, $isOptional = false) {
		$this->name = $name;
		$this->value = $value;
		$this->isOptional = $isOptional;
	}

	/**
	 * Parse this node.
	 * Sets the variable in the current context.
	 * @param SassContext the context in which this node is parsed
	 * @return array the parsed node - an empty array
	 */
	public function parse($context) {
		if (!$this->isOptional || !$context->hasVariable($this->name)) {
				$context->setVariable(
					$this->name, $this->evaluate($this->value, $context));
		}
		return array();
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
		if (empty($matches[self::NAME]) || ($matches[self::VALUE] === '')) {
			throw new SassVariableNodeException("Invalid variable definition; name and expression required.\nLine {$line['number']}: " . (is_array($line['file']) ? join(DIRECTORY_SEPARATOR, $line['file']) : ''));
		}
		return $matches;
	}
}