<?php
/* SVN FILE: $Id: SassRootNode.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * SassRootNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

require_once(dirname(__FILE__).'/../script/SassScriptParser.php');
require_once(dirname(__FILE__).'/../renderers/SassRenderer.php');

/**
 * SassRootNode class.
 * Also the root node of a document.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassRootNode extends SassNode {
	/**
	 * @var SassScriptParser SassScript parser
	 */
	protected $parser;
	/**
	 * @var SassRenderer the renderer for this node
	 */
	protected $renderer;
	/**
	 * @var array options
	 */
	protected $options;

	/**
	 * Root SassNode constructor.
	 * @param array options for the tree
	 * @return SassNode
	 */
	public function __construct($options) {
		$this->root = $this;
		$this->options = $options;
		$this->parser = new SassScriptParser();
		$this->renderer = SassRenderer::getRenderer($this->options['style']);
		$this->line = array('indentLevel' => -1);
	}

	/**
	 * Parses this node and its children into the render tree.
	 * Dynamic nodes are evaluated, files imported, etc.
	 * Only static nodes for rendering are in the resulting tree.
	 * @param SassContext the context in which this node is parsed
	 * @return SassNode root node of the render tree
	 */
	public function parse($context) {
		$node = clone $this;
		foreach ($this->children as $child) {
			$node->children = array_merge($node->children, $child->parse($context));
		} // foreach
		return $node;
	}

	/**
	 * Render this node.
	 * @return string the rendered node
	 */
	public function render() {
		$node = $this->parse(new SassContext());
		$output = '';
		foreach ($node->children as $child) {
			$output .= $child->render();
		} // foreach
		return $output;
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
