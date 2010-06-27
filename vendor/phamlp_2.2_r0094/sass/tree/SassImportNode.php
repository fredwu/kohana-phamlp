<?php
/* SVN FILE: $Id: SassImportNode.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * SassImportNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

/**
 * SassImportNode class.
 * Represents a CSS Import.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassImportNode extends SassNode {
	const IDENTIFIER = '@';
	const MATCH = '/^@import\s+(.+)/';
	const MATCH_CSS = '/^(url\(|")/';
	const URI = 1;

	/**
	 * @var string uri to import
	 */
	private $uri;

	/**
	 * SassImportNode.
	 * @param SassNode the parent of this node
	 * @param array source line for this node
	 * @return SassImportNode
	 */
	public function __construct($uri) {
		$this->uri = $uri;
	}

	/**
	 * Parse this node.
	 * If a CSS import returns the import rule.
	 * Else returns the rendered tree for the file.
	 * @param SassContext the context in which this node is parsed
	 * @return array the parsed node
	 */
	public function parse($context) {
		if (preg_match(self::MATCH_CSS, $this->uri)) {
			return "@import {$this->uri}";
		}
		else {
			$tree = SassFile::getTree(
				SassFile::getFile($this->uri, $this->options), $this->options);
			if (empty($tree)) {
				throw new SassImportNodeException("Unable to create document tree for {$this->uri}");
			}
			else {
				return $tree->parse($context)->children;
			}
		}
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