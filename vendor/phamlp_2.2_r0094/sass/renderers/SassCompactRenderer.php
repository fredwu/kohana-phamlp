<?php
/* SVN FILE: $Id: SassCompactRenderer.php 67 2010-04-18 11:51:40Z chris.l.yates $ */
/**
 * SassCompactRenderer class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.renderers
 */

require_once('SassCompressedRenderer.php');

/**
 * SassCompactRenderer class.
 * Each CSS rule takes up only one line, with every property defined on that
 * line. Nested rules are placed next to each other with no newline, while
 * groups of rules have newlines between them.
 * @package			PHamlP
 * @subpackage	Sass.renderers
 */
class SassCompactRenderer extends SassCompressedRenderer {
	/**
	 * Renders the brace between the selectors and the properties
	 * @return string the brace between the selectors and the properties
	 */
	protected function between() {
	  return ' { ';
	}

	/**
	 * Renders the brace at the end of the rule
	 * @return string the brace between the rule and its properties
	 */
	protected function end() {
	  return " }\n";
	}

	/**
	 * Renders a comment.
	 * Comments preceeding a rule are on their own line.
	 * Comments within a rule are on the same line as the rule.
	 * @param SassNode the node being rendered
	 * @return string the rendered commnt
	 */
	public function renderComment($node) {
		$nl = ($node->parent instanceof SassRuleNode?'':"\n");
	  return "$nl/* " . join("\n * ", $node->children) . " */$nl" ;
	}

	/**
	 * Renders a directive.
	 * @param SassNode the node being rendered
	 * @param array properties of the directive
	 * @return string the rendered directive
	 */
	public function renderDirective($node, $properties) {
		return str_replace("\n", '', parent::renderDirective($node, $properties)) .
			"\n\n";
	}

	/**
	 * Renders properties.
	 * @param array properties to render
	 * @return string the rendered properties
	 */
	public function renderProperties($properties) {
		return join(' ', $properties);
	}

	/**
	 * Renders a property.
	 * @param SassNode the node being rendered
	 * @return string the rendered property
	 */
	public function renderProperty($node) {
		return "{$node->name}: {$node->value};";
	}

	/**
	 * Renders a rule.
	 * @param SassNode the node being rendered
	 * @param array rule properties
	 * @param string rendered rules
	 * @return string the rendered rule
	 */
	public function renderRule($node, $properties, $rules) {
		return parent::renderRule($node, $properties,
			str_replace("\n\n", "\n", $rules)) . "\n";
	}

	/**
	 * Renders rule selectors.
	 * @param SassNode the node being rendered
	 * @return string the rendered selectors
	 */
	protected function renderSelectors($node) {
		$selectors = '';
		foreach ($node->selectors as $line) {
			$selectors .= join(', ', $line) . ",";
		} // foreach
	  return substr($selectors, 0, -1);
	}
}