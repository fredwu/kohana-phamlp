<?php
/* SVN FILE: $Id: SassNumber.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * SassNumber class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.script.literals
 */

/**
 * SassNumber class.
 * Provides operations and type testing for Sass numbers.
 * Units are of the passed value are converted the those of the class value
 * if it has units. e.g. 2cm + 20mm = 4cm while 2 + 20mm = 22mm.
 * @package			PHamlP
 * @subpackage	Sass.script.literals
 */
class SassNumber extends SassLiteral {
	/**
	 * Regx for matching and extracting numbers
	 */
	const MATCH = '/^((?:-)?(?:\d*\.)?\d+)([a-zA-Z%]+)?/';
	const VALUE = 1;
	const UNITS = 2;
	/**
	 * The number of decimal digits to round to.
	 * If the units are pixels the result is always
	 * rounded down to the nearest integer.
	 */
	const PRECISION = 4;

	/**
	 * @var array allowable number units
	 */
	static private $cssUnits =
		array('%', 'em', 'ex', 'px', 'in', 'cm', 'mm', 'pt', 'pc');

	/**
	 * @var array Conversion factors for units using inches as the base unit
	 * (only because pt and px are expressed as fraction of an inch, so makes the
	 * numbers easy to undertand).
	 * Conversions are based on the following
	 * in: inches — 1 inch is equal to 2.54 centimeters.
   * cm: centimeters
   * mm: millimeters
   * pt: points — the points used by CSS 2.1 are equal to 1/72nd of an inch.
   * pc: picas — 1 pica is equal to 12 points.
   * px: 1/96th in
   * em: 16px
   * ex: 0.5em.
	 */
	static private $unitConversion = array(
		'em' => 6,
		'ex' => 3,
		'px' => 96,
		'in' => 1,
		'cm' => 2.54,
		'mm' => 25.4,
		'pt' => 72,
		'pc' => 6
	);

	/**
	 * @var string units of this number
	 */
	private $units = '';

	/**
	 * class constructor.
	 * Sets the value and units of the number.
	 * @param string number
	 * @return SassNumber
	 */
	public function __construct($value) {
	  preg_match(self::MATCH, $value, $matches);
	  $this->value = $matches[self::VALUE];
	  if (!empty($matches[self::UNITS])) {
	  	if (!in_array($matches[self::UNITS], self::$cssUnits)) {
				throw new SassNumberException("Invalid units: $value");
	  	}
			$this->units = $matches[self::UNITS];
	  }
	}

	/**
	 * Adds the value of other to the value of this
	 * @param string value to add
	 * @return mixed SassNumber if other is a SassNumber or
	 * SassColour if it is a SassColour
	 */
	public function _add($other) {
		if ($other instanceof SassColour) {
			return $other->_add($this);
		}
		else {
			$other = $this->convertUnits($other);
			$this->value += $other->value;
			return $this;
		}
	}

	/**
	 * Subtracts the value of other from this value
	 * @param string value to subtract
	 * @return mixed SassNumber if other is a SassNumber or
	 * SassColour if it is a SassColour
	 */
	public function _subtract($other) {
		if ($other instanceof SassColour) {
			return $other->_subtract($this);
		}
		else {
			$other = $this->convertUnits($other);
			$this->value -= $other->value;
			return $this;
		}
	}

	/**
	 * Multiplies this value by the value of other
	 * @param string value to multiply by
	 * @return mixed SassNumber if other is a SassNumber or
	 * SassColour if it is a SassColour
	 */
	public function _multiply($other) {
		if ($other instanceof SassColour) {
			return $other->_multiply($this);
		}
		else {
			$other = $this->convertUnits($other);
			$this->value *= $other->value;
			return $this;
		}
	}

	/**
	 * Divides this value by the value of other
	 * @param string value to divide by
	 * @return mixed SassNumber if other is a SassNumber or
	 * SassColour if it is a SassColour
	 */
	public function _divide($other) {
		if ($other instanceof SassColour) {
			return $other->_divide($this);
		}
		else {
			$other = $this->convertUnits($other);
			$this->value /= $other->value;
			return $this;
		}
	}
	/**
	 * The SassScript == operation.
	 * @return SassBoolean SassBoolean object with the value true if the values
	 * of this and other are equal, false if they are not
	 */
	public function _eq($other) {
		$other = $this->convertUnits($other);
		return new SassBoolean(($this->value == $other->value ? 'true' : 'false'));
	}
	/**
	 * The SassScript != operation.
	 * @return SassBoolean SassBoolean object with the value true if the values
	 * of this and other are not equal, false if they are
	 */
	public function _neq($other) {
		$other = $this->convertUnits($other);
		return new SassBoolean(($this->value != $other->value ? 'true' : 'false'));
	}

	/**
	 * Takes the modulus (remainder) of this value divided by the value of other
	 * @param string value to divide by
	 * @return mixed SassNumber if other is a SassNumber or
	 * SassColour if it is a SassColour
	 */
	public function _modulus($other) {
		if ($other instanceof SassColour) {
			return $other->_modulus($this);
		}
		else {
			$other = $this->convertUnits($other);
			$this->value %= $other->value;
			return $this;
		}
	}

	/**
	 * Converts the other number to this numbers units.
	 * @param SassNumber the other number
	 * @return SassNumber the other number with converted to this numbers units
	 * @throws SassNumberException if other is not a SassNumber or the units are
	 * incompatible
	 */
	private function convertUnits($other) {
		if (!$other instanceof SassNumber) {
			throw new SassNumberException("Second operand is not a number");
		}
		if ($other->hasUnits()) {
		  if (!$this->hasUnits()) {
				$this->units = $other->units;
		  }
			elseif ($other->units != $this->units) {
				if (array_key_exists($this->units, self::$unitConversion) &&
						array_key_exists($other->units, self::$unitConversion)) {
					$other->value *=
						(self::$unitConversion[$this->units] /
						 self::$unitConversion[$other->units]);
					$other->units = $this->units;
				}
				else {
					throw new SassNumberException("Incompatible units: {$this->units} and {$other->units}");
				}
			}
		}
		return $other;
	}

	/**
	 * Returns a value indicating if this nimber has units.
	 * @return boolean true if this number has units, false if not
	 */
	public function hasUnits() {
	  return !empty($this->units);
	}

	/**
	 * Returns the value of this number.
	 * @return mixed float if a unitless number, otherwise string
	 */
	public function getValue() {
		return $this->hasUnits() ? $this->toString() : $this->value;
	}

	/**
	 * Converts the number to a string with it's units if any.
	 * If the units are px the result is rounded down to the nearest integer,
	 * otherwise the result is rounded to the specified precision.
 	 * @return string number as a string with it's units if any
	 */
	public function toString() {
	  return ($this->units == 'px' ? floor($this->value) :
	  		round($this->value, self::PRECISION)) .
	  	$this->units;
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