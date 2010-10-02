<?php
/* SVN FILE: $Id: HamlHelperNode.php 92 2010-05-20 17:42:59Z chris.l.yates $ */
/**
 * HamlHelperNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.tree
 */

/**
 * HamlHelperNode class.
 * Represent a helper in the Haml source.
 * The helper is run on the output from child nodes when the node is rendered.
 * @package			PHamlP
 * @subpackage	Haml.tree
 */
class HamlHelperNode extends HamlNode {
	const REGEX_HELPER = '/(.*?)(\w+)\(((?:array\(.+?\))?.*?)\)/';
	/**
	 * @var string the helper class name
	 */
	private $helperClass;
	/**
	 * @var string the helper method and its arguments
	 */
	private $helperMethod;

	/**
	 * HamlFilterNode constructor.
	 * Sets the filter.
	 * @param string helper class.
	 * @param string helper call.
	 * @return HamlHelperNode
	 */
	public function __construct($helperClass, $helperMethod) {
	  $this->helperClass = $helperClass;
	  $this->helperMethod = $helperMethod;
	}

	/**
	* Render this node.
	* The filter is run on the content of child nodes before being returned.
	* @return string the rendered node
	*/
	public function render() {
		$output = '';
		foreach ($this->children as $child) {
			$output .= trim($child->render());
		} // foreach
		preg_match(self::REGEX_HELPER, $this->helperMethod, $matches);
		$output = '<?php '.(empty($matches[1]) ? 'echo' : $matches[1])." {$this->helperClass}::".$matches[2]."('$output',{$matches[3]}); ?>";
		return $this->debug($output);
	}
}