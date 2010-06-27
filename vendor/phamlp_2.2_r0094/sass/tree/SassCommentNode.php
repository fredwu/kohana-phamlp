<?php
/* SVN FILE: $Id: SassCommentNode.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * SassCommentNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

/**
 * SassCommentNode class.
 * Represents a CSS comment.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassCommentNode extends SassNode {
	const MATCH = '%^/\*\s*(.*)$%';
	const COMMENT = 1;
	const IDENTIFIER = '/';
	const CSS_COMMENT = '*';
	const Sass_COMMENT = '/';

	/**
	 * CommentNode constructor.
	 * @param string first line of comment
	 * @return CommentNode
	 */
	public function __construct($comment) {
		$this->addLine($comment);
	}

	/**
	 * Adds a comment line to the node.
	 * @return SassNode the line to add
	 * @return SassNode this node
	 */
	public function addLine($line) {
		$this->children[] = $line;
		return $this;
	}

	/**
	 * Parse this node.
	 * @return array the parsed node - an empty array
	 */
	public function parse($context) {
		return array($this);
	}

	/**
	 * Render this node.
	 * @return string the rendered node
	 */
	public function render() {
		$this->indentLevel = $this->parent->indentLevel + 1;
		return $this->renderer->renderComment($this);
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