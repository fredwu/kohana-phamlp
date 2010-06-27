<?php
/**
 * SassScript functions class file.
 * 
 * Methods in this module are accessible from the SassScript context.
 * For example, you can write:
 *
 * !colour = hsl(120, 100%, 50%)
 *
 * and it will call SassFunctions::hsl().
 *
 * There are a few things to keep in mind when modifying this module.
 * First of all, the arguments passed are SassLiteral objects.
 * Literal objects are also expected to be returned.
 *
 * Most Literal objects support the SassLiteral->value accessor
 * for getting their values.
 * Colour objects, though, must be accessed using {Sass::Script::Colour *rgb rgb}.
 *
 * Second, making functions accessible from Sass introduces the temptation
 * to do things like database access within stylesheets.
 * This temptation must be resisted.
 * Keep in mind that Sass stylesheets are only compiled once
 * and then left as static CSS files.
 * Any dynamic CSS should be left in `<style>` tags in the HTML.
 * 
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.script
 */
 
/**
 * SassScript functions class.
 * A collection of functions for use in SassSCript.
 * The following functions are provided:
 * + <b>hsl()</b>: Converts an `hsl(hue, saturation, lightness)` triplet into a colour.
 * + <b>rgb()</b>: Converts an `rgb(red, green, blue)` triplet into a colour.
 * + <b>percentage()</b>: Converts a unitless number to a percentage.
 * + <b>round()</b>: Rounds a number to the nearest whole number.
 * + <b>ceil()</b>: Rounds a number up to the nearest whole number.
 * + <b>floor()</b>: Rounds a number down to the nearest whole number.
 * + <b>abs()</b>: Returns the absolute value of a number.
 * @package			PHamlP
 * @subpackage	Sass.script
 */
class SassScriptFunctions {
	/**
	 * Creates a {Colour} object from red, green, and blue values.
	 * @param mixed the red component.
	 * A number between 0 and 255 inclusive, or between 0% and 100% inclusive
	 * @param mixed the green component.
	 * A number between 0 and 255 inclusive, or between 0% and 100% inclusive
	 * @param mixed the blue component.
	 * A number between 0 and 255 inclusive, or between 0% and 100% inclusive
	 * @return SassColour SassColour object
	 * @throws SassScriptFunctionException if red, green, or blue are out of bounds
	 */
	public function rgb($red, $green, $blue) {
		$rgb = array('Red' => $red, 'Green' => $green, 'Blue' => $blue);
		foreach ($rgb as $colour => &$value) {
			if (substr($value, -1) == '%') {
				$value = substr($value, 0, -1);
				if ($value < 0 || $value > 100) {
					throw new SassScriptFunctionException("$colour value must be between 0% and 100% inclusive");
				}
				$value = round($value * 2.55);
			}
			else {
				if (!is_numeric($value) || $value < 0 || $value > 255) {
					throw new SassScriptFunctionException("$colour value must be between 0 and 255 inclusive");
				}
			}
		} // foreach
		return new SassColour($rgb);
	}

	/**
	 * Creates a SassColour object from hue, saturation, and lightness.
	 * Uses the algorithm from the
	 * {@link http://www.w3.org/TR/css3-color/#hsl-color CSS3 spec}.
	 *
	 * @param float The hue of the colour in degrees.
	 * Should be between 0 and 360 inclusive
	 * @param mixed The saturation of the colour as a percentage.
	 * Must be between '0%' and `100%`, inclusive
	 * @param mixed The lightness of the colour as a percentage.
	 * Must be between `0%` and `100%`, inclusive
	 * @return SassColour The resulting colour
	 * @throws SassScriptFunctionException if `saturation` or `lightness` are out of bounds
	 */
	public function hsl($h, $s, $l) {
		$s = floatval($s);
		$l = floatval($l);
		if ($s < 0 || $s > 100)
			throw new SassScriptFunctionException("Saturation must be between 0% and 100%");
		if ($l < 0 || $l > 100)
			throw new SassScriptFunctionException("Lightness must be between 0% and 100%");

		$h = ($h % 360) / 360;
		$s /= 100;
		$l /= 100;

		$m2 = ($l <= 0.5 ? $l * ($s + 1) : $l + $s - $l * $s);
		$m1 = $l * 2 - $m2;
		return new SassColour(array(
			round(self::hue_to_rgb($m1, $m2, $h + 1/3) * 0xff),
			round(self::hue_to_rgb($m1, $m2, $h)       * 0xff),
			round(self::hue_to_rgb($m1, $m2, $h - 1/3) * 0xff)
		));
	}

	/**
	 * Converts a decimal number to a percentage.
	 * For example:
	 *
	 *		 percentage(100px / 50px) => 200%
	 *
	 * @param SassNumber The decimal number to convert to a percentage
	 * @return SassNumber The number as a percentage
	 * @throws SassScriptFunctionException If `$value` isn't a unitless number
	 */
	public function percentage($value) {
		if (!$value instanceof SassNumber || $value->hasUnits()) {
			throw new SassScriptFunctionException(__METHOD__ . ": {$value->value} is not a number");
		}
		$value->value *= 100;
		$value->units = '%';
		return $value;
	}

	/**
	 * Rounds a number to the nearest whole number.
	 * For example:
	 *
	 *		 round(10.4px) => 10px
	 *		 round(10.6px) => 11px
	 *
	 * @param SassNumber The number to round
	 * @return SassNumber The rounded number
	 * @throws SassScriptFunctionException If `$value` isn't a number
	 */
	public function round($value) {
		if (!$value instanceof SassNumber) {
			throw new SassScriptFunctionException(__METHOD__ . ": {$value->value} is not a number");
		}
		$value->value = round($value->value);
		return $value;
	}

	/**
	 * Rounds a number up to the nearest whole number.
	 * For example:
	 *
	 *		 ciel(10.4px) => 11px
	 *		 ciel(10.6px) => 11px
	 *
	 * @param SassNumber The number to round
	 * @return SassNumber The rounded number
	 * @throws SassScriptFunctionException If `$value` isn't a number
	 */
	public function ceil($value) {
		if (!$value instanceof SassNumber) {
			throw new SassScriptFunctionException(__METHOD__ . ": {$value->value} is not a number");
		}
		$value->value = ceil($value->value);
		return $value;
	}

	/**
	 * Rounds down to the nearest whole number.
	 * For example:
	 *
	 *		 floor(10.4px) => 10px
	 *		 floor(10.6px) => 10px
	 *
	 * @param SassNumber The number to round
	 * @return SassNumber The rounded number
	 * @throws SassScriptFunctionException If `$value` isn't a number
	 */
	public function floor($value) {
		if (!$value instanceof SassNumber) {
			throw new SassScriptFunctionException(__METHOD__ . ": {$value->value} is not a number");
		}
		$value->value = floor($value->value);
		return $value;
	}

	/**
	 * Finds the absolute $value of a number.
	 * For example:
	 *
	 *		 abs(10px) => 10px
	 *		 abs(-10px) => 10px
	 *
	 * @param SassNumber The number to round
	 * @return SassNumber The absolute $value of the number
	 * @throws SassScriptFunctionException If `$value` isn't a number
	 */
	public function abs($value) {
		if (!$value instanceof SassNumber) {
			throw new SassScriptFunctionException(__METHOD__ . ": {$value->value} is not a number");
		}
		$value->value = abs($value->value);
		return $value;
	}

	/**
	 * Helper function for hsl().
	 * @param float $m1
	 * @param float $m2
	 * @param float $h
	 * @return float
	 */
	private function hue_to_rgb($m1, $m2, $h) {
		$h += ($h < 0 ? 1 : 0);
		$h -= ($h > 1 ? 1 : 0);
		if ($h * 6 < 1) {
			return $m1 + ($m2 - $m1) * $h * 6;
		}
		elseif ($h * 2 < 1) {
			return $m2;
		}
		elseif ($h * 3 < 2) {
			return $m1 + ($m2 - $m1) * (2/3 - $h) * 6;
		}
		else {
			return $m1;
		}
	}
}
