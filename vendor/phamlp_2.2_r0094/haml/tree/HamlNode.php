<?php
/* SVN FILE: $Id: HamlNode.php 92 2010-05-20 17:42:59Z chris.l.yates $ */
/**
 * HamlNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.tree
 */

require_once('HamlRootNode.php');
require_once('HamlCommentNode.php');
require_once('HamlDoctypeNode.php');
require_once('HamlElementNode.php');
require_once('HamlFilterNode.php');
require_once('HamlHelperNode.php');
require_once('HamlCodeBlockNode.php');
require_once('HamlNodeExceptions.php');

/**
 * HamlNode class.
 * Base class for all Haml nodes.
 * @package			PHamlP
 * @subpackage	Haml.tree
 */
class HamlNode {
	/**
	 * @var HamlNode root node of this node
	 */
	protected $root;
	/**
	 * @var HamlNode parent of this node
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
	 * @var boolean whether to show the output in the browser for debug
	 */
	public $showOutput;
	/**
	 * @var boolean whether to show the source in the browser for debug
	 */
	public $showSource;
	/**
	 * @var string content to render
	 */
	public $content;
	/**
	 * @var string output buffer
	 */
	protected $output;

	public function __construct($content) {
	  $this->content = $content;
	}

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
		throw new HamlNodeException("No getter function for $name");
	}

	/**
	 * Setter.
	 * @param string name of property to set
	 * @return mixed value of property
	 * @return HamlNode this node
	 */
	public function __set($name, $value) {
		$setter = 'set' . ucfirst($name);
		if (method_exists($this, $setter)) {
			$this->$setter($value);
			return $this;
		}
		throw new HamlNodeException("No setter function for $name");
	}

	/**
	 * Return a value indicating if this node has a parent
	 * @return array the node's parent
	 */
	public function hasParent() {
		return !empty($this->parent);
	}

	/**
	 * Returns the node's content and that of its child nodes
	 * @param integer the indent level. This is to allow properly indented output
	 * that filters (e.g. Sass) may need.
	 * @return string the node's content and that of its child nodes
	 */
	public function getContent($indentLevel = 0) {
		$output = str_repeat(' ', 2 * $indentLevel++) . $this->content . "\n";
		foreach ($this->children as $child) {
			$output .= $child->getContent($indentLevel);
		} // foreach
		return $output;
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
	 * @return HamlNode the child to add
	 * @return HamlNode this node
	 */
	public function addChild($child, $parent = null) {
		$child->parent		= $this;
		$child->root			= (empty($parent) ? $this->root : $parent->root);
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
	 * @return HamlNode the last child node of this node
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
	 * @return HamlNode this node
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
		return $this->line['indentLevel'];
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
	  $o = $this->root->options;
	  return $this->root->options;
	}

	/**
	 * Returns the renderer.
	 * @return HamlRenderer the rendered
	 */
	public function getRenderer() {
	  return $this->root->renderer;
	}

	public function render() {
		$output = $this->renderer->renderContent($this);
		foreach ($this->children as $child) {
			$output .= $child->render();
		} // foreach
		return $this->debug($output);
	}

	protected function debug($output) {
		$output = ($this->showSource ? $this->showSource($output) : $output);
		return ($this->showOutput && $this->line['indentLevel'] == 0 ?
			nl2br(str_replace(' ', '&nbsp;', htmlspecialchars($output))) :
			$output);
	}

	/**
	 * Adds a comment with source debug information for the current line to the output.
	 * The debug information is:
	 * + source file (relative to the application path)
	 * + line number
	 * + indent level
	 * + source code
	 * @param array source line(s) that generated the ouput
	 */
	protected function showSource($output) {
		return "<!--\n  ({$this->line['file']} {$this->line['number']}:{$this->line['indentLevel']})\n  {$this->line[HamlParser::HAML_SOURCE]}\n-->\n$output";
	}
}