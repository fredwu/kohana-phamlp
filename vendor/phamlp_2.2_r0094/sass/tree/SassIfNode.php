<?php
/* SVN FILE: $Id: SassIfNode.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * SassIfNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

/**
 * SassIfNode class.
 * Represents Sass If, Else If and Else statements.
 * Else If and Else statement nodes are chained below the If statement node.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassIfNode extends SassNode {
	const MATCH_IF = '/^@if\s+(.+)$/';
	const MATCH_ELSE = '/@else(\s+if\s+(.+))?/';
	const IF_EXPRESSION = 1;
	const ELSE_IF = 1;
	const ELSE_EXPRESSION = 2;
	/**
	 * @var SassIfNode the next else node.
	 */
	private $else;
	/**
	 * @var string expression to evaluate
	 */
	private $expression;
	/**
	 * @var string current value of the expression
	 */
	private $value;

	/**
	 * SassIfNode constructor.
	 * @param string expression to evaluate. Null if this is an "else" node
	 * @return SassIfNode
	 */
	public function __construct($expression = null) {
		$this->expression = $expression;
	}

	/**
	 * Adds an "else" statement to this node.
	 * @param SassIfNode "else" statement node to add
	 * @return SassIfNode this node
	 */
	public function addElse($node) {
	  if (is_null($this->else)) {
	  	$node->root			= $this->root;
	  	$node->parent		= $this->parent;
			$this->else			= $node;
	  }
	  else {
			$this->else->addElse($node);
	  }
	  return $this;
	}

	/**
	 * Parse this node.
	 * @param SassContext the context in which this node is parsed
	 * @return array parsed child nodes
	 */
	public function parse($context) {
		$children = array();

		if ($this->isElse() || $this->evaluate($this->expression, $context)) {
			foreach ($this->children as $child) {
				$children = array_merge($children, $child->parse($context));
			} // foreach
		}
		elseif (!empty($this->else)) {
			$children = $this->else->parse($context);
		}
		return $children;
	}

	/**
	 * Returns a value indicating if this node is an "else" node.
	 * @return true if this node is an "else" node, false if this node is an "if"
	 * or "else if" node
	 */
	private function isElse() {
	  return empty($this->expression);
	}

	/**
	 * Returns the matches for this type of node.
	 * @param array the line to match
	 * @return array matches
	 */
	static public function matchIf($line) {
		preg_match(self::MATCH_IF, $line['source'], $matches);
		return $matches;
	}

	/**
	 * Returns the matches for this type of node.
	 * @param array the line to match
	 * @return array matches
	 */
	static public function matchElse($line) {
		preg_match(self::MATCH_ELSE, $line['source'], $matches);
		return $matches;
	}
}