<?php
/* SVN FILE: $Id: HamlPreserveFilter.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * Preserve Filter for {@link http://haml-lang.com/ Haml} class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright		Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.filters
 */

/**
 * Preserve Filter for {@link http://haml-lang.com/ Haml} class.
 * Does not parse the filtered text and preserves line breaks.
 * @package			PHamlP
 * @subpackage	Haml.filters
 */
class HamlPreserveFilter extends HamlBaseFilter {
	/**
	 * Run the filter
	 * @param string text to filter
	 * @return string filtered text
	 */
	public function run($text) {
	  return str_replace("\n", '&#x000a',
	  	preg_replace(HamlParser::MATCH_INTERPOLATION, '<?php echo \1; ?>', $text)
	  ) . "\n";
	}
}