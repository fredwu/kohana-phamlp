<?php
/* SVN FILE: $Id: SassForNode.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * SassForNode class file.
 * This is an enhanced version of the standard SassScript @for loop that adds
 * an optional step clause. Step must evaluate to a positive integer.
 * The syntax is:
 * <pre>@for <var> from <start> to|through <end>[ step <step>]</pre>.
 *
 * <start> can be less or greater than <end>.
 * If the step clause is ommitted the <step> = 1.
 * <var> is available to the rest of the script following evaluation
 * and has the value that terminated the loop.
 * 
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree 
 */

/**
 * SassForNode class.
 * Represents a Sass @for loop.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassForNode extends SassNode {
	const MATCH = '/@for\s+!(\w+)\s+from\s+(.+?)\s+(through|to)\s+(.+?)(?:\s+step\s+(.+))?$/';

	const VARIABLE = 1;
	const FROM = 2;
	const INCLUSIVE = 3;
	const TO = 4;
	const STEP = 5;
	const IS_INCLUSIVE = 'to';

	/**
	 * @var string variable name for the loop
	 */
	private $variable;
	/**
	 * @var string expression that provides the loop start value
	 */
	private $from;
	/**
	 * @var string expression that provides the loop end value
	 */
	private $to;
	/**
	 * @var boolean whether the loop end value is inclusive
	 */
	private $inclusive;
	/**
	 * @var string expression that provides the amount by which the loop variable
	 * changes on each iteration
	 */
	private $step;

	/**
	 * SassForNode constructor.
	 * @var string variable name for the loop
	 * @var string expression that provides the loop start value
	 * @var string expression that provides the loop end value
	 * @var boolean whether the loop end value is inclusive
	 * @var string expression that provides the amount by which the loop variable
	 * changes on each iteration

	 * @return SassForNode
	 */
	public function __construct($variable, $from, $to, $inclusive, $step) {
		$this->variable = $variable;
		$this->from = $from;
		$this->to = $to;
		$this->inclusive = $inclusive;
		$this->step = $step;
	}

	/**
	 * Parse this node.
	 * @param SassContext the context in which this node is parsed
	 * @return array parsed child nodes
	 */
	public function parse($context) {
		$children = array();
		$from = $this->evaluate($this->from, $context);
		$to = $this->evaluate($this->to, $context);
		$step = $this->evaluate($this->step, $context) * ($to > $from ? 1 : -1);

		if ($this->inclusive) {
			$to += ($from < $to ? 1 : -1);
		}

		$context = new SassContext($context);
		for ($i = $from; ($from < $to ? $i < $to : $i > $to); $i = $i + $step) {
			$context->setVariable($this->variable, $i);
			foreach ($this->children as $child) {
				$children = array_merge($children, $child->parse($context));
			} // foreach
		}
		return $children;
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
