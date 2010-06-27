<?php
/* SVN FILE: $Id: SassRuleNode.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * SassRuleNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

/**
 * SassRuleNode class.
 * Represents a CSS rule.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassRuleNode extends SassNode {
	const MATCH = '/^(.+?)(?:\s*\{)?$/';
	const SELECTOR = 1;
	const CONTINUED = ',';

	/**
	 * @const string that is replaced with the parent node selector
	 */
	const PARENT = '&';

	/**
	 * @var array rule selector(s). Each entry in the array is a line of comma
	 * separated selectors. Lines that are to continue must end in a comma.
	 */
	private $selectors;

	/**
	 * @var array rule selector(s). Each entry in the array is an array of
	 * selectors that were on a line
	 */
	private $_selectors = array();

	/**
	 * SassRuleNode constructor.
	 * @param string rule selector
	 * @return SassRuleNode
	 */
	public function __construct($selector) {
		$this->addSelectors($selector);
	}

	/**
	 * Adds a selector or selectors to the rule.
	 * If the selectors are to continue for the rule the selector must end in a comma
	 * @param string selector
	 */
	public function addSelectors($selector) {
		$this->selectors[] = $selector;
	}

	/**
	 * Returns a value indicating if the selectors for this rule are to be continued.
	 * @param boolean true if the selectors for this rule are to be continued,
	 * false if not
	 */
	protected function getIsContinued() {
		return substr($this->selectors[count($this->selectors) - 1], -1) ===
			self::CONTINUED;
	}

	/**
	 * Parse this node and its children into static nodes.
	 * @param SassContext the context in which this node is parsed
	 * @return array the parsed node and its children
	 */
	public function parse($context) {
		$this->_selectors = array();
		$parentSelectors = $this->getParentSelectors();

		foreach ($this->selectors as $line) {
			$selectors = $this->explode($line);
			$_selectors = array();

			foreach ($selectors as $selector) {
				$selector = $this->interpolate(trim($selector), $context);

				if ($this->parentReferencePos($selector) !== false) {
					if (!empty($parentSelectors)) {
						foreach ($parentSelectors as $parentSelectorLine) {
							foreach ($parentSelectorLine as $parentSelector) {
								$_selectors[] = $this->replaceParentReference($selector, $parentSelector);
							} // foreach
						} // foreach
					}
					else {
						throw new SassRuleNodeException('Can not use parent selector (' .
							self::PARENT . ") when no parent selectors.\n
							Line {$this->line['number']}: {$this->line['filename']}");
					}
				}
				elseif (!empty($parentSelectors)) {
					foreach ($parentSelectors as $parentSelectorLine) {
						foreach ($parentSelectorLine as $parentSelector) {
							$_selectors[] = "$parentSelector $selector";
						} // foreach
					} // foreach
				}
				else {
					$_selectors[] = $selector;
				}
			} // foreach

			$this->_selectors[] = $_selectors;
		} // foreach

		$node = clone $this;
		foreach ($this->children as $child) {
			$node->children = array_merge($node->children, $child->parse($context));
		} // foreach
		return array($node);
	}

	/**
	 * Render this node and its children to CSS.
	 * @return string the rendered node
	 */
	public function render() {
		$rules = '';
		$properties = array();
		$this->indentLevel = $this->parent->indentLevel + 1;

		foreach ($this->children as $child) {
			$child->parent = $this;
			if ($child instanceof SassRuleNode) {
				$rules .= $child->render();
			}
			else {
				$properties[] = $child->render();
			}
		} // foreach

		return $this->renderer->renderRule($this, $properties, $rules);
	}

	/**
	 * Returns the selectors array
	 * @return array selectors
	 */
	public function getSelectors() {
		return $this->_selectors;
	}

	/**
	 * Returns the parent selector for this node.
	 * This in an empty string if there is no parent selector.
	 * @return the parent selector for this node
	 */
	private function getParentSelectors() {
		$selector = array();
		$ancestor = $this->getParent();
		while (!$ancestor instanceof SassRuleNode && $ancestor->hasParent()) {
			$ancestor = $ancestor->getParent();
		}

	  if ($ancestor instanceof SassRuleNode) {
			$selector = $ancestor->getSelectors();
		}

	  return $selector;
	}

	/**
	 * Returns the position of the first parent reference in the selector.
	 * If there is no parent reference in the selector this function returns
	 * boolean FALSE.
	 * Note that the return value may be non-Boolean that evaluates to FALSE,
	 * i.e. 0. The return value should be tested using the === operator.
	 * @param string selector to test
	 * @return mixed integer: position of the the first parent reference,
	 * boolean: false if there is no parent reference.
	 */
	private function parentReferencePos($selector) {
		$inString = false;
		for ($i = 0, $l = strlen($selector); $i < $l; $i++) {
			$c = $selector[$i];
			if ($c === self::PARENT && !$inString) {
				return $i;
			}
			elseif ($c == '"') {
				$inString = !$inString;
			}
		}
	  return false;
	}

	/**
	 * Replaces parent references in the selector with the parent selector
	 * @param string selector
	 * @param string parent selector
	 * @return string selector with parent references replaced
	 */
	private function replaceParentReference($selector, $parentSelector) {
		while (($pos = $this->parentReferencePos($selector)) !== false) {
			$selector = substr($selector, 0, $pos) . $parentSelector . substr($selector, $pos + 1);
		}
		return $selector;
	}

	/**
	 * Explodes a string of selectors into an array.
	 * We can't use PHP::explode as this will potentially explode attribute
	 * matches in the selector, e.g. div[title="some,value"]
	 * @param string selectors
	 * @return array selectors
	 */
	private function explode($string) {
		$selectors = array();
		$inString = false;
		$selector = '';
		for ($i = 0, $l = strlen($string); $i < $l; $i++) {
			$c = $string[$i];
			if ($c === ',' && !$inString) {
				$selectors[] = trim($selector);
				$selector = '';
			}
			else {
				if ($c == '"') {
					$inString = !$inString;
				}
				$selector .= $c;
			}
		}

		if (!empty($selector)) {
			$selectors[] = trim($selector);
		}

	  return $selectors;
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