<?php
/* SVN FILE: $Id: SassLiteral.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * SassLiteral class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.script.literals
 */
require_once('SassBoolean.php');
require_once('SassColour.php');
require_once('SassNumber.php');
require_once('SassString.php');
require_once('SassLiteralExceptions.php');

/**
 * SassLiteral class.
 * Base class for all Sass literals.
 * Sass data types are extended from this class.
 * @package			PHamlP
 * @subpackage	Sass.script.literals
 */
abstract class SassLiteral {
	/**
	 * @var string value of the literal type
	 */
  protected $value;

	/**
	 * class constructor
	 * @param string value of the literal type
	 * @return SassLiteral
	 */
	public function __construct($value = null) {
		$this->value = $value;
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
		else {
			throw new SassLiteralException("No getter function for $name.");
		}
	}

	protected function getValue() {
		throw new SassLiteralException(get_class($this) . ' must override getValue() method');
	}

	/**
	 * Returns the boolean representation of the value of this
	 * @return boolean the boolean representation of the value of this
	 */
	protected function getBoolean() {
		return (boolean)$this->value;
	}

	/**
	 * Adds the value of other to the value of this
	 * @param string value to add
	 * @return SassLiteral result
	 * @throws Exception if addition not supported for the data type
	 */
	public function _add($other) {
		throw new SassLiteralException(get_class($this) . ' does not support Addition.');
	}

	/**
	 * Subtracts the value of other from the value of this
	 * @param string value to subtract
	 * @return SassLiteral result
	 * @throws Exception if subtraction not supported for the data type
	 */
	public function _subtract($other) {
		throw new SassLiteralException(get_class($this) . ' does not support Subtraction');
	}

	/**
	 * Multiplies the value of this by the value of other
	 * @param string value to multiply by
	 * @return SassLiteral result
	 * @throws Exception if multiplication not supported for the data type
	 */
	public function _multiply($other) {
		throw new SassLiteralException(get_class($this) . ' does not support Multiplication');
	}

	/**
	 * Divides the value of this by the value of other
	 * @param string value to divide by
	 * @return SassLiteral result
	 * @throws Exception if division not supported for the data type
	 */
	public function _divide($other) {
		throw new SassLiteralException(get_class($this) . ' does not support Division');
	}

	/**
	 * Takes the modulus (remainder) of this value divided by the value of other
	 * @param string value to divide by
	 * @return SassLiteral result
	 * @throws Exception if modulus not supported for the data type
	 */
	public function _modulus($other) {
		throw new SassLiteralException(get_class($this) . ' does not support Modulus');
	}

	/**
	 * Bitwise AND the value of other and this value
	 * @param string value to bitwise AND with
	 * @return string result
	 * @throws Exception if bitwise AND not supported for the data type
	 */
	public function _bw_and($other) {
		throw new SassLiteralException(get_class($this) . ' does not support Bitwise AND');
	}

	/**
	 * Bitwise OR the value of other and this value
	 * @param string value to bitwise OR with
	 * @return string result
	 * @throws Exception if bitwise OR not supported for the data type
	 */
	public function _bw_or($other) {
		throw new SassLiteralException(get_class($this) . ' does not support Bitwise OR');
	}

	/**
	 * Bitwise XOR the value of other and the value of this
	 * @param string value to bitwise XOR with
	 * @return string result
	 * @throws Exception if bitwise XOR not supported for the data type
	 */
	public function _bw_xor($other) {
		throw new SassLiteralException(get_class($this) . ' does not support Bitwise XOR');
	}

	/**
	 * Bitwise NOT the value of other and the value of this
	 * @param string value to bitwise NOT with
	 * @return string result
	 * @throws Exception if bitwise NOT not supported for the data type
	 */
	public function _bw_not() {
		throw new SassLiteralException(get_class($this) . ' does not support Bitwise NOT');
	}

	/**
	 * Shifts the value of this left by the number of bits given in value
	 * @param string amount to shift left by
	 * @return string result
	 * @throws Exception if bitwise Shift Left not supported for the data type
	 */
	public function _shiftl($other) {
		throw new SassLiteralException(get_class($this) . ' does not support Bitwise Shift Left');
	}

	/**
	 * Shifts the value of this right by the number of bits given in value
	 * @param string amount to shift right by
	 * @return string result
	 * @throws Exception if bitwise Shift Right not supported for the data type
	 */
	public function _shiftr($other) {
		throw new SassLiteralException(get_class($this) . ' does not support Bitwise Shift Right');
	}

	/**
	 * The SassScript and operation.
	 * @return SassBoolean SassBoolean object with the value true if the boolean
	 * of this the boolean of other are both true, false if not
	 */
	public function _and($other) {
		return new SassBoolean(($this->boolean and $other->boolean ?
			'true' : 'false'));
	}
	/**
	 * The SassScript or operation.
	 * @return SassBoolean SassBoolean object with the value true if either the
	 * boolean of this and/or the boolean of other are true, false if not
	 */
	public function _or($other) {
		return new SassBoolean(($this->boolean or $other->boolean ?
			'true' : 'false'));
	}
	/**
	 * The SassScript xor operation.
	 * @return SassBoolean SassBoolean object with the value true if either the
	 * boolean of this or the boolean of other, but not both, is true, false if not
	 */
	public function _xor($other) {
		return new SassBoolean(($this->boolean xor $other->boolean ?
			'true' : 'false'));
	}
	/**
	 * The SassScript not operation.
	 * @return SassBoolean SassBoolean object with the value true if the
	 * boolean of this is false or false if it is true
	 */
	public function _not($other) {
		return new SassBoolean(($this->boolean ? 'false' : 'true'));
	}
	/**
	 * The SassScript > operation.
	 * @return SassBoolean SassBoolean object with the value true if the values
	 * of this is greater than the value of other, false if it is not
	 */
	public function _gt($other) {
		return new SassBoolean(($this->value > $other->value ? 'true' : 'false'));
	}
	/**
	 * The SassScript >= operation.
	 * @return SassBoolean SassBoolean object with the value true if the values
	 * of this is greater than or equal to the value of other, false if it is not
	 */
	public function _gte($other) {
		return new SassBoolean(($this->value >= $other->value ? 'true' : 'false'));
	}
	/**
	 * The SassScript < operation.
	 * @return SassBoolean SassBoolean object with the value true if the values
	 * of this is less than the value of other, false if it is not
	 */
	public function _lt($other) {
		return new SassBoolean(($this->value < $other->value ? 'true' : 'false'));
	}
	/**
	 * The SassScript <= operation.
	 * @return SassBoolean SassBoolean object with the value true if the values
	 * of this is less than or equal to the value of other, false if it is not
	 */
	public function _lte($other) {
		return new SassBoolean(($this->value <= $other->value ? 'true' : 'false'));
	}
	/**
	 * The SassScript == operation.
	 * @return SassBoolean SassBoolean object with the value true if the values
	 * of this and other are equal, false if they are not
	 */
	public function _eq($other) {
		return new SassBoolean(($this->value == $other->value ? 'true' : 'false'));
	}
	/**
	 * The SassScript != operation.
	 * @return SassBoolean SassBoolean object with the value true if the values
	 * of this and other are not equal, false if they are
	 */
	public function _neq($other) {
		return new SassBoolean(($this->value != $other->value ? 'true' : 'false'));
	}
	/**
	 * The SassScript default operation (e.g. !a !b, "foo" "bar").
	*/
	public function _concat($other) {}

	/**
	 * Returns a string representation of the value.
	 * @return string string representation of the value.
	 */
	abstract public function toString();

	/**
	 * Returns a value indicating if a token of this type can be matched at
	 * the start of the subject string.
	 * @param string the subject string
	 * @return mixed match at the start of the string or false if no match
	 */
	abstract static public function isa($subject);
}