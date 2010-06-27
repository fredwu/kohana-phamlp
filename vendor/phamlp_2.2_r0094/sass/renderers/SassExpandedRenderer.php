<?php
/* SVN FILE: $Id: SassExpandedRenderer.php 67 2010-04-18 11:51:40Z chris.l.yates $ */
/**
 * SassExpandedRenderer class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.renderers
 */

require_once('SassCompactRenderer.php');

/**
 * SassExpandedRenderer class.
 * Expanded is the typical human-made CSS style, with each property and rule
 * taking up one line. Properties are indented within the rules, but the rules
 * are not indented in any special way.
 * @package			PHamlP
 * @subpackage	Sass.renderers
 */
class SassExpandedRenderer extends SassCompactRenderer {
	/**
	 * Renders the brace between the selectors and the properties
	 * @return string the brace between the selectors and the properties
	 */
	protected function between() {
	  return " {\n" ;
	}
	
	/**
	 * Renders the brace at the end of the rule
	 * @return string the brace between the rule and its properties
	 */
	protected function end() {
	  return "\n}\n\n";
	}

	/**
	 * Returns the indent string for the node
	 * @param SassNode the node to return the indent string for
	 * @return string the indent string for this SassNode
	 */
	protected function getIndent($node) {
		if ($node instanceof SassRuleNode) {
			return ($node->inDirective() ? self::INDENT : '');
		}
		if ($node instanceof SassPropertyNode) {
			return self::INDENT . ($node->inDirective() ? self::INDENT : '');
		}
		elseif ($node instanceof SassCommentNode && 
				$node->parent instanceof SassRuleNode) {
			return self::INDENT . $this->getIndent($node->parent);
		}
		else {
			return '';
		}
	}

	/**
	 * Renders a comment.
	 * @param SassNode the node being rendered
	 * @return string the rendered commnt
	 */
	public function renderComment($node) {
		$indent = $this->getIndent($node);
		$comment = join("\n * ", $node->children);
		$nl = empty($indent)?"\n":'';
	  return "$indent/* $comment */$nl";
	}

	/**
	 * Renders properties.
	 * @param array properties to render
	 * @return string the rendered properties
	 */
	public function renderProperties($properties) {
		return join("\n", $properties);
	}

	/**
	 * Renders a property.
	 * @param SassNode the node being rendered
	 * @return string the rendered property
	 */
	public function renderProperty($node) {
		return $this->getIndent($node) .
		parent::renderProperty($node);
	}
}