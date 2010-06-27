<?php
/* SVN FILE: $Id: SassNode.php 78 2010-05-03 15:08:28Z chris.l.yates $ */
/**
 * SassNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

require_once('SassContext.php');
require_once('SassCommentNode.php');
require_once('SassDirectiveNode.php');
require_once('SassImportNode.php');
require_once('SassMixinNode.php');
require_once('SassMixinDefinitionNode.php');
require_once('SassPropertyNode.php');
require_once('SassRootNode.php');
require_once('SassRuleNode.php');
require_once('SassVariableNode.php');
require_once('SassForNode.php');
require_once('SassIfNode.php');
require_once('SassWhileNode.php');
require_once('SassNodeExceptions.php');

/**
 * SassNode class.
 * Base class for all Sass nodes.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
abstract class SassNode {
	const MATCH_INTERPOLATION = '/(?<!\\\\)#\{(.*?)\}/';
	const MATCH_VARIABLE = '/(?<!\\\\)!(\w+)/';

	/**
	 * @var SassNode root node of this node
	 */
	protected $root;
	/**
	 * @var SassNode parent of this node
	 */
	protected $parent;
	/**
	 * @var array children of this node
	 */
	protected $children = array();
	/**
	 * @var array source line
	 */
	public $line;

	/**
	 * Getter.
	 * @param string name of property to get
	 * @return mixed return value of getter function
	 */
	public function __get($name) {
		$getter = 'get' . ucfirst($name);
		if (method_exists($this, $getter)) {
			return $this->$getter();
		}
		throw new SassNodeException("No getter function for $name");
	}

	/**
	 * Setter.
	 * @param string name of property to set
	 * @return mixed value of property
	 * @return SassNode this node
	 */
	public function __set($name, $value) {
		$setter = 'set' . ucfirst($name);
		if (method_exists($this, $setter)) {
			$this->$setter($value);
			return $this;
		}
		throw new SassNodeException("No setter function for $name");
	}

	/**
	 * Resets children when cloned
	 * @see parse
	 */
	public function __clone() {
		$this->children = array();
	}

	/**
	 * Return a value indicating if this node has a parent
	 * @return array the node's parent
	 */
	public function hasParent() {
		return !empty($this->parent);
	}

	/**
	 * Returns the node's parent
	 * @return array the node's parent
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Adds a child to this node.
	 * @return SassNode the child to add
	 * @return SassNode this node
	 */
	public function addChild($child) {
		$child->parent		= $this;
		$child->root			= $this->root;
		$this->children[] = $child;
		return $this;
	}

	/**
	 * Returns a value indicating if this node has children
	 * @return boolean true if the node has children, false if not
	 */
	public function hasChildren() {
		return !empty($this->children);
	}

	/**
	 * Returns the node's children
	 * @return array the node's children
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * Returns the last child node of this node.
	 * @return SassNode the last child node of this node
	 */
	public function getLastChild() {
	  return $this->children[count($this->children) - 1];
	}

	/**
	 * Returns the indent level of this node.
	 * @return integer the indent level of this node
	 */
	private function getIndentLevel() {
		return $this->line['indentLevel'];
	}

	/**
	 * Sets the indent level of this node.
	 * Used during rendering to give correct indentation.
	 * @param integer the indent level of this node
	 * @return SassNode this node
	 */
	private function setIndentLevel($level) {
		$this->line['indentLevel'] = $level;
		return $this;
	}

	/**
	 * Returns the source for this node
	 * @return string the source for this node
	 */
	private function getSource() {
		return $this->line['source'];
	}

	/**
	 * Returns the source for this node
	 * @return string the source for this node
	 */
	private function getLineNumber() {
		return $this->line['number'];
	}

	/**
	 * Returns the filename for this node
	 * @return string the filename for this node
	 */
	private function getFilename() {
		return join(DIRECTORY_SEPARATOR, $this->line['file']);
	}

	/**
	 * Returns the options.
	 * @return array the options
	 */
	public function getOptions() {
	  return $this->root->options;
	}

	/**
	 * Returns the property syntax being used.
	 * @return string the property syntax being used
	 */
	public function getPropertySyntax() {
	  return $this->root->options['propertySyntax'];
	}

	/**
	 * Returns the render style of the document tree.
	 * @return string the render style of the document tree
	 */
	public function getStyle() {
	  return $this->root->options['style'];
	}

	/**
	 * Returns the SassScript parser.
	 * @return SassScriptParser the SassScript parser
	 */
	public function getParser() {
	  return $this->root->parser;
	}

	/**
	 * Returns the renderer.
	 * @return SassRenderer the rendered
	 */
	public function getRenderer() {
	  return $this->root->renderer;
	}

	/**
	 * Returns a value indicating whether this node is in a directive
	 * @param boolean true if the node is in a directive, false if not
	 */
	public function inDirective() {
		return $this->parent instanceof SassDirectiveNode ||
				$this->parent instanceof SassDirectiveNode;
	}

	/**
	 * Returns a value indicating whether this node is in a SassScript directive
	 * @param boolean true if this node is in a SassScript directive, false if not
	 */
	public function inSassScriptDirective() {
		return $this->parent instanceof SassForNode ||
				$this->parent->parent instanceof SassForNode ||
				$this->parent instanceof SassIfNode ||
				$this->parent->parent instanceof SassIfNode ||
				$this->parent instanceof SassWhileNode ||
				$this->parent->parent instanceof SassWhileNode;
	}

	/**
	 * Replace interpolated SassScript contained in '#{}' with the parsed value.
	 * @param string the text to interpolate
	 * @param SassContext the context in which the string is interpolated
	 * @return string the interpolated text
	 */
	protected function interpolate($string, $context) {
		for ($i = 0, $n = preg_match_all(self::MATCH_INTERPOLATION, $string, $matches);
				$i < $n; $i++) {
			$matches[1][$i] = $this->evaluate($matches[1][$i], $context);
		}
	  return str_replace($matches[0], $matches[1], $string);
	}

	/**
	 * Parses a Sass script expression.
	 * @param string expression to parse
	 * @param SassContext the context in which the expression is parsed
	 * @return string value of parsed expression
	 */
	protected function evaluate($expression, $context) {
	  return $this->parser->parse(
	  	$this->substituteVariables($expression, $context));
	}

	/**
	 * Substitute variables in an expression with their values.
	 * @param string expression to substitue variables in
	 * @param SassContext the context for variable substitution
	 * @return string expression with variables substitued
	 */
	private function substituteVariables($string, $context) {
		for ($i = 0, $n = preg_match_all(self::MATCH_VARIABLE, $string, $matches);
				$i < $n; $i++) {
			$var = $context->getVariable($matches[1][$i]);
			if (is_bool($var)) {
				$var = ($var ? 'true' : 'false');
			}
			elseif (!(SassColour::isa($var) || SassNumber::isa($var))) {
				$var = "\"$var\"";
			}
			$string = str_replace($matches[0][$i], $var, $string);
		}
	  return $string;
	}

	/**
	 * Returns a value indicating if the line represents this type of node.
	 * Child classes must override this method.
	 * @throws SassNodeException if not overriden
	 */
	static public function isa($line) {
		throw new SassNodeException('Child classes must override this method');
	}

	/**
	 * Returns the matches for this type of node.
	 * Child classes must override this method.
	 * @throws SassNodeException if not overriden
	 */
	static public function match($line) {
		throw new SassNodeException('Child classes must override this method');
	}
}