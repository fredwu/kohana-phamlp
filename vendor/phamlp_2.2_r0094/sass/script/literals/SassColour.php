<?php
/* SVN FILE: $Id: SassColour.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * SassColour class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.script.literals
 */

/**
 * SassColour class.
 * Provides operations and type testing for Sass colours.
 * Colour operations are all piecewise, e.g. when adding two colours each
 * component is added independantly; Rr = R1 + R2, Gr = G1 + G2, Br = B1 + B2.
 * Resulting colours are returned as a named colour if possible or #rrggbb.
 *
 * Transparent is a SassColour. Operations on transparent return transparent.
 *
 * @package			PHamlP
 * @subpackage	Sass.script.literals
 */
class SassColour extends SassLiteral {
	/**@#+
	 * Regexes for matching and extracting colours
	 */
	const MATCH = '/^((#([\da-f]{6}|[\da-f]{3}))|transparent|{CSS_COLOURS})/';
	const EXTRACT_3 = '/#([\da-f])([\da-f])([\da-f])/';
	const EXTRACT_6 = '/#([\da-f]{2})([\da-f]{2})([\da-f]{2})/';
	const TRANSPARENT = 'transparent';

	/**@#-*/
	static private $svgColours = array(
		'aliceblue'							=> '#f0f8ff',
		'antiquewhite'					=> '#faebd7',
		'aqua'									=> '#00ffff',
		'aquamarine'						=> '#7fffd4',
		'azure'									=> '#f0ffff',
		'beige'									=> '#f5f5dc',
		'bisque'								=> '#ffe4c4',
		'black'									=> '#000000',
		'blanchedalmond'				=> '#ffebcd',
		'blue'									=> '#0000ff',
		'blueviolet'						=> '#8a2be2',
		'brown'									=> '#a52a2a',
		'burlywood'							=> '#deb887',
		'cadetblue'							=> '#5f9ea0',
		'chartreuse'						=> '#7fff00',
		'chocolate'							=> '#d2691e',
		'coral'									=> '#ff7f50',
		'cornflowerblue'				=> '#6495ed',
		'cornsilk'							=> '#fff8dc',
		'crimson'								=> '#dc143c',
		'cyan'									=> '#00ffff',
		'darkblue'							=> '#00008b',
		'darkcyan'							=> '#008b8b',
		'darkgoldenrod'					=> '#b8860b',
		'darkgray'							=> '#a9a9a9',
		'darkgreen'							=> '#006400',
		'darkgrey'							=> '#a9a9a9',
		'darkkhaki'							=> '#bdb76b',
		'darkmagenta'						=> '#8b008b',
		'darkolivegreen'				=> '#556b2f',
		'darkorange'						=> '#ff8c00',
		'darkorchid'						=> '#9932cc',
		'darkred'								=> '#8b0000',
		'darksalmon'						=> '#e9967a',
		'darkseagreen'					=> '#8fbc8f',
		'darkslateblue'					=> '#483d8b',
		'darkslategray'					=> '#2f4f4f',
		'darkslategrey'					=> '#2f4f4f',
		'darkturquoise'					=> '#00ced1',
		'darkviolet'						=> '#9400d3',
		'deeppink'							=> '#ff1493',
		'deepskyblue'						=> '#00bfff',
		'dimgray'								=> '#696969',
		'dimgrey'								=> '#696969',
		'dodgerblue'						=> '#1e90ff',
		'firebrick'							=> '#b22222',
		'floralwhite'						=> '#fffaf0',
		'forestgreen'						=> '#228b22',
		'fuchsia'								=> '#ff00ff',
		'gainsboro'							=> '#dcdcdc',
		'ghostwhite'						=> '#f8f8ff',
		'gold'									=> '#ffd700',
		'goldenrod'							=> '#daa520',
		'gray'									=> '#808080',
		'green'									=> '#008000',
		'greenyellow'						=> '#adff2f',
		'grey'									=> '#808080',
		'honeydew'							=> '#f0fff0',
		'hotpink'								=> '#ff69b4',
		'indianred'							=> '#cd5c5c',
		'indigo'								=> '#4b0082',
		'ivory'									=> '#fffff0',
		'khaki'									=> '#f0e68c',
		'lavender'							=> '#e6e6fa',
		'lavenderblush'					=> '#fff0f5',
		'lawngreen'							=> '#7cfc00',
		'lemonchiffon'					=> '#fffacd',
		'lightblue'							=> '#add8e6',
		'lightcoral'						=> '#f08080',
		'lightcyan'							=> '#e0ffff',
		'lightgoldenrodyellow'	=> '#fafad2',
		'lightgray'							=> '#d3d3d3',
		'lightgreen'						=> '#90ee90',
		'lightgrey'							=> '#d3d3d3',
		'lightpink'							=> '#ffb6c1',
		'lightsalmon'						=> '#ffa07a',
		'lightseagreen'					=> '#20b2aa',
		'lightskyblue'					=> '#87cefa',
		'lightslategray'				=> '#778899',
		'lightslategrey'				=> '#778899',
		'lightsteelblue'				=> '#b0c4de',
		'lightyellow'						=> '#ffffe0',
		'lime'									=> '#00ff00',
		'limegreen'							=> '#32cd32',
		'linen'									=> '#faf0e6',
		'magenta'								=> '#ff00ff',
		'maroon'								=> '#800000',
		'mediumaquamarine'			=> '#66cdaa',
		'mediumblue'						=> '#0000cd',
		'mediumorchid'					=> '#ba55d3',
		'mediumpurple'					=> '#9370db',
		'mediumseagreen'				=> '#3cb371',
		'mediumslateblue'				=> '#7b68ee',
		'mediumspringgreen'			=> '#00fa9a',
		'mediumturquoise'				=> '#48d1cc',
		'mediumvioletred'				=> '#c71585',
		'midnightblue'					=> '#191970',
		'mintcream'							=> '#f5fffa',
		'mistyrose'							=> '#ffe4e1',
		'moccasin'							=> '#ffe4b5',
		'navajowhite'						=> '#ffdead',
		'navy'									=> '#000080',
		'oldlace'								=> '#fdf5e6',
		'olive'									=> '#808000',
		'olivedrab'							=> '#6b8e23',
		'orange'								=> '#ffa500',
		'orangered'							=> '#ff4500',
		'orchid'								=> '#da70d6',
		'palegoldenrod'					=> '#eee8aa',
		'palegreen'							=> '#98fb98',
		'paleturquoise'					=> '#afeeee',
		'palevioletred'					=> '#db7093',
		'papayawhip'						=> '#ffefd5',
		'peachpuff'							=> '#ffdab9',
		'peru'									=> '#cd853f',
		'pink'									=> '#ffc0cb',
		'plum'									=> '#dda0dd',
		'powderblue'						=> '#b0e0e6',
		'purple'								=> '#800080',
		'red'										=> '#ff0000',
		'rosybrown'							=> '#bc8f8f',
		'royalblue'							=> '#4169e1',
		'saddlebrown'						=> '#8b4513',
		'salmon'								=> '#fa8072',
		'sandybrown'						=> '#f4a460',
		'seagreen'							=> '#2e8b57',
		'seashell'							=> '#fff5ee',
		'sienna'								=> '#a0522d',
		'silver'								=> '#c0c0c0',
		'skyblue'								=> '#87ceeb',
		'slateblue'							=> '#6a5acd',
		'slategray'							=> '#708090',
		'slategrey'							=> '#708090',
		'snow'									=> '#fffafa',
		'springgreen'						=> '#00ff7f',
		'steelblue'							=> '#4682b4',
		'tan'										=> '#d2b48c',
		'teal'									=> '#008080',
		'thistle'								=> '#d8bfd8',
		'tomato'								=> '#ff6347',
		'turquoise'							=> '#40e0d0',
		'violet'								=> '#ee82ee',
		'wheat'									=> '#f5deb3',
		'white'									=> '#ffffff',
		'whitesmoke'						=> '#f5f5f5',
		'yellow'								=> '#ffff00',
		'yellowgreen'						=> '#9acd32'
	);

	/**
	 * @var array reverse array (value => name) of named SVG1.0 colours
	 */
	static private $_svgColours;

	/**
	* @var array reverse array (value => name) of named HTML4 colours
	*/
	static private $_html4Colours = array(
		'#000000' => 'black',
		'#000080' => 'navy',
		'#0000ff' => 'blue',
		'#008000' => 'green',
		'#008080' => 'teal',
		'#00ff00' => 'lime',
		'#00ffff' => 'aqua',
		'#800000' => 'maroon',
		'#800080' => 'purple',
		'#808000' => 'olive',
		'#808080' => 'gray',
		'#c0c0c0' => 'silver',
		'#ff0000' => 'red',
		'#ff00ff' => 'fuchsia',
		'#ffff00' => 'yellow',
		'#ffffff' => 'white',
	);

	static private $regex;

	/**
	 * class constructor.
	 * @param mixed $value
	 * @return SassColour
	 */
	public function __construct($value) {
		$this->value = $this->getComponents($value);
		if (empty(self::$_svgColours)) {
			self::$_svgColours = array_flip(self::$svgColours);
		}
	}

	/**
	 * Colour addition
	 * @param mixed value (SassColour or SassNumber) to add
	 * @return sassColour the colour result
	 */
	public function _add($other) {
		if (!empty($this->value)) {
			$rgb = $this->getComponents($other);
			foreach ($rgb as $n => $component) {
				$this->value[$n] += $component;
			} // foreach
		}

		return $this;
	}

	/**
	 * Colour subraction
	 * @param mixed value (SassColour or SassNumber) to subtract
	 * @return sassColour the colour result
	 */
	public function _subtract($other) {
		if (!empty($this->value)) {
			$rgb = $this->getComponents($other);
			foreach ($rgb as $n => $component) {
				$this->value[$n] -= $component;
			} // foreach
		}

		return $this;
	}

	/**
	 * Colour multiplication
	 * @param mixed value (SassColour or SassNumber) to multiply by
	 * @return sassColour the colour result
	 */
	public function _multiply($other) {
		if (!empty($this->value)) {
			$rgb = $this->getComponents($other);
			foreach ($rgb as $n => $component) {
				$this->value[$n] *= $component;
			} // foreach
		}

		return $this;
	}

	/**
	 * Colour division
	 * @param mixed value (SassColour or SassNumber) to divide by
	 * @return sassColour the colour result
	 */
	public function _divide($other) {
		if (!empty($this->value)) {
			$rgb = $this->getComponents($other);
			foreach ($rgb as $n => $component) {
				$this->value[$n] /= $component;
			} // foreach
		}

		return $this;
	}

	/**
	 * Colour modulus
	 * @param mixed value (SassColour or SassNumber) to divide by
	 * @return sassColour the colour result
	 */
	public function _modulus($other) {
		if (!empty($this->value)) {
			$rgb = $this->getComponents($other);
			foreach ($rgb as $n => $component) {
				$this->value[$n] %= $component;
			} // foreach
		}

		return $this;
	}

	/**
	 * Colour bitwise AND
	 * @param mixed value (SassColour or SassNumber) to bitwise AND with
	 * @return sassColour the colour result
	 */
	public function _bw_and($other) {
		if (!empty($this->value)) {
			$rgb = $this->getComponents($other);
			foreach ($rgb as $n => $component) {
				$this->value[$n] &= $component;
			} // foreach
		}

		return $this;
	}

	/**
	 * Colour bitwise OR
	 * @param mixed value (SassColour or SassNumber) to bitwise OR with
	 * @return sassColour the colour result
	 */
	public function _bw_or($other) {
		if (!empty($this->value)) {
			$rgb = $this->getComponents($other);
			foreach ($rgb as $n => $component) {
				$this->value[$n] |= $component;
			} // foreach
		}

		return $this;
	}

	/**
	 * Colour bitwise XOR
	 * @param mixed value (SassColour or SassNumber) to bitwise XOR with
	 * @return sassColour the colour result
	 */
	public function _bw_xor($other) {
		if (!empty($this->value)) {
			$rgb = $this->getComponents($other);
			foreach ($rgb as $n => $component) {
				$this->value[$n] ^= $component;
			} // foreach
		}

		return $this;
	}

	/**
	 * Colour bitwise NOT
	 * @return sassColour the colour result
	 */
	public function _not() {
		if (!empty($this->value)) {
			foreach ($this->value as &$component) {
				$component = ~$component;
			} // foreach
		}

		return $this;
	}

	/**
	 * Colour bitwise Shift Left
	 * @param sassNumber amount to shift left by
	 * @return sassColour the colour result
	 */
	public function _shiftl($other) {
		if (!$other instanceof SassNumber || $other->hasUnits()) {
			throw new SassColourException('Amount to shift left by must be a unitless number');
		}

		if (!empty($this->value)) {
			foreach ($this->value as &$component) {
				$component >> $other->value;
			} // foreach
		}

		return $this;
	}

	/**
	 * Colour bitwise Shift Right
	 * @param sassNumber amount to shift right by
	 * @return sassColour the colour result
	 */
	public function _shiftr($other) {
		if (!$other instanceof SassNumber || $other->hasUnits()) {
			throw new SassColourException('Amount to shift right by must be a unitless number');
		}

		if (!empty($this->value)) {
			foreach ($this->value as &$component) {
				$component >> $other->value;
			} // foreach
		}

		return $this;
	}

	/**
	 * Returns an array with the RGB components of this colour.
	 * @return array the RGB components of this colour
	 */
	public function getRgb() {
		return $this->value;
	}

	/**
	 * Returns the value of this colour.
	 * @param boolean whether to use CSS3 SVG1.0 colour names
	 * @return string the colour
	 */
	public function getValue($css3 = false) {
		return $this->toString($css3);
	}

	/**
	 * Converts the colour to a string.
	 * @param boolean whether to use CSS3 SVG1.0 colour names
 	 * @return string the colour as a named colour or #rrggbb
	 */
	public function toString($css3 = false) {
		if (empty($this->value)) {
			return 'transparent';
		}
		$r = round(abs($this->value[0]));
		$g = round(abs($this->value[1]));
		$b = round(abs($this->value[2]));
		$r = $r > 255 ? $r % 255 : $r;
		$g = $g > 255 ? $g % 255 : $g;
		$b = $b > 255 ? $b % 255 : $b;
		$colour = sprintf('#%02x%02x%02x', $r, $g, $b);
		if ($css3) {
			return (array_key_exists($colour, self::$svgColours) ?
				self::$_svgColours[$colour] : $colour);				}
		else {
			return (array_key_exists($colour, self::$_html4Colours) ?
				self::$_html4Colours[$colour] : $colour);
		}
	}

	/**
	 * Returns an array with the RGB components of the colour.
	 * @param string colour
	 * @return array RGB components of the colour
	 */
	private function getComponents($colour) {
		if ($colour instanceof SassColour) {
			return $colour->rgb;
		}
		elseif ($colour instanceof SassNumber) {
			if ($colour->hasUnits()) {
			}
			return array($colour->value, $colour->value, $colour->value);
		}
		elseif (is_array($colour)) {
			foreach ($colour as $component) {
				if ($component < 0 || $component > 255) {
					throw new SassColourException('Colour RGB values must be between 0 and 255 inclusive.');
				}
			} // foreach
			return $colour;
		}
		elseif (is_string($colour)) {
			if ($colour == self::TRANSPARENT) {
				return array();
			}
			if (array_key_exists($colour = strtolower($colour), self::$svgColours)) {
				$colour = self::$svgColours[$colour];
			}

			if (strlen($colour) == 4) {
				preg_match(self::EXTRACT_3, $colour, $matches);
				for ($i = 1; $i < 4; $i++) {
					$matches[$i] = str_repeat($matches[$i], 2);
				}
			}
			else {
				preg_match(self::EXTRACT_6, $colour, $matches);
			}

			array_shift($matches);
			foreach ($matches as &$match) {
				$match = intval($match, 16);
			} // foreach
			return $matches;
		}
		else {
			throw new SassColourException("Invalid data type.");
		}
	}

	/**
	 * Returns a value indicating if a token of this type can be matched at
	 * the start of the subject string.
	 * @param string the subject string
	 * @return mixed match at the start of the string or false if no match
	 */
	static public function isa($subject) {
		if (empty(self::$regex)) {
			self::$regex = str_replace('{CSS_COLOURS}', join('|', array_reverse(array_keys(self::$svgColours))), self::MATCH);
		}
		return (preg_match(self::$regex, strtolower($subject), $matches) ?
			$matches[0] : false);
	}
}
