<?php
/* SVN FILE: $Id: SassScriptOperation.php 75 2010-04-23 18:44:18Z chris.l.yates $ */
/**
 * SassScriptOperation class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.script
 */

/**
 * SassScriptOperation class.
 * The operation to perform.
 * @package			PHamlP
 * @subpackage	Sass.script
 */
class SassScriptOperation {
  const MATCH = '/^(\(|\)|\+|-|\*|\/|%|<=|>=|<|>|==|!=|=|#{|}|and|or|xor|not|!|&|\||\^|~)/';

	/**
	 * @var array map symbols to tokens.
	 * A token is function, associativity, precedence, number of operands
	 */
	static public $operators = array(
		'*'		=> array('multiply',	'l', 9, 2),
		'/'		=> array('divide',		'l', 9, 2),
		'%'		=> array('modulus',		'l', 9, 2),
		'+'		=> array('add',				'l', 8, 2),
		'-'		=> array('subtract',	'l', 8, 2),
		'<<'	=> array('shiftl',		'l', 7, 2),
		'>>'	=> array('shiftr',		'l', 7, 2),
		'<='	=> array('lte',				'l', 6, 2),
		'>='	=> array('gte',				'l', 6, 2),
		'<'		=> array('lt',				'l', 6, 2),
		'>'		=> array('gt',				'l', 6, 2),
		'=='	=> array('eq',				'l', 5, 2),
		'!='	=> array('neq',				'l', 5, 2),
		'&'		=> array('bw_and',		'l', 4, 2),
		'|'		=> array('bw_or',			'l', 4, 2),
		'^'		=> array('bw_xor',		'l', 4, 2),
		'~'		=> array('bw_not',		'r', 4, 1),
		'and'	=> array('and',				'l', 3, 2),
		'or'	=> array('or',				'l', 3, 2),
		'xor'	=> array('xor',				'l', 3, 2),
		'not'	=> array('not',				'l', 3, 1),
		'!'		=> array('not',				'l', 3, 1),
		'='		=> array('assign',		'r', 2, 2),
		')'		=> array('rparen',		'l', 1),
		'('		=> array('lparen',		'l', 0),
		','		=> array('comma'),
		'#{'	=> array('begin_interpolation'),
		'}'	=> array('end_interpolation'),
	);

	/**
	 * @var string operator for this operation
	 */
	private $operator;
	/**
	 * @var string associativity of the operator; left or right
	 */
	private $associativity;
	/**
	 * @var integer precedence of the operator
	 */
	private $precedence;
	/**
	 * @var integer number of operands required by the operator
	 */
	private $operandCount;

	/**
	 * SassScriptOperation constructor
	 *
	 * @param array operation to perform
	 * @return SassScriptOperation
	 */
	public function __construct($operation) {
		$this->operator			 = self::$operators[$operation][0];
		if (isset(self::$operators[$operation][1])) {
			$this->associativity = self::$operators[$operation][1];
			$this->precedence		 = self::$operators[$operation][2];
			$this->operandCount	 = (isset(self::$operators[$operation][3]) ?
					self::$operators[$operation][3] : null);
		}
	}

	/**
	 * Getter function for properties
	 * @param string name of property
	 * @return mixed value of the property
	 * @throws SassScriptOperationException if the property does not exist
	 */
	public function __get($name) {
		if (property_exists($this, $name)) {
			return $this->$name;
		}
	  else {
			throw new SassScriptOperationException("Unknown property: $name");
	  }
	}

	/**
	 * Performs this operation.
	 * @param array operands for the operation. The operands are SassLiterals
	 * @return SassLiteral the result of the operation
	 * @throws SassScriptOperationException if the oprand count is incorrect or
	 * the operation is undefined
	 */
	public function perform($operands) {
		if (count($operands) !== $this->operandCount) {
			throw new SassScriptOperationException("Incorrect number of operands for " . get_class($operands[0]) . " Expected {$this->operandCount}, received " . count($operands));
		}

		$operation = "_{$this->operator}";
		if (method_exists($operands[0], $operation)) {
			return $operands[0]->$operation($operands[1]);
		}

		throw new SassScriptOperationException("Undefined operation \"$operation\" for " . get_class($operands[0]));
	}

	/**
	 * Returns a value indicating if a token of this type can be matched at
	 * the start of the subject string.
	 * @param string the subject string
	 * @return mixed match at the start of the string or false if no match
	 */
	static public function isa($subject) {
		return (preg_match(self::MATCH, $subject, $matches) ? $matches[0] : false);
	}
}