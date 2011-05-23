<?php defined('SYSPATH') or die('No direct script access.');
return array
(
	'parsers' => array('haml', 'sass'),
	'lib_dir' => 'd4rky-pl-phamlp', // depends on phamlp version
	'haml'    => array
	(
		'cache_dir' => 'haml', // cache_dir is a directory within APPPATH.'cache/'
		'extension' => 'haml', // HAML template file extension
		'options'   => array
		(
			'attrWrapper'         => '"',
			'debug'               => 0,
			'doctype'             => null,
			'emptyTags'           => array('meta', 'img', 'link', 'br', 'hr', 'input', 'area', 'param', 'col', 'base'),
			'escapeHtml'          => false,
			'filterDir'           => null,
			'format'              => 'html5',
			'inlineTags'          => array('a', 'abbr', 'accronym', 'b', 'big', 'cite', 'code', 'dfn', 'em', 'i', 'kbd', 'q', 'samp', 'small', 'span', 'strike', 'strong', 'tt', 'u', 'var'),
			'minimizedAttributes' => array('compact', 'checked', 'declare', 'readonly', 'disabled', 'selected', 'defer', 'ismap', 'nohref', 'noshade', 'nowrap', 'multiple', 'noresize'),
			'preserve'            => array('pre', 'textarea'),
			'preserveComments'    => false,
			'style'               => 'nested',
			'suppressEval'        => false,
			'ugly'                => false,
			'helperFile'          => null,
		)
	),
	
	'sass'    => array
	(
		'cache_dir' => 'sass', // cache_dir is a directory within APPPATH.'cache/'
		'scss_dir'   => 'sass', // scss_dir is a directory within APPPATH.'views/'
		'css_dir'   => 'css', // css_dir is a directory within DOCROOT
		'options'   => array
		(
			'cache'               => TRUE,
			'debug_info'          => FALSE,
			'style'               => 'nested',  // nested, expanded, compact or compressed
			'quiet'               => FALSE, 
			'vendor_properties'   => array(), // @see http://code.google.com/p/phamlp/wiki/SassOptions#Default_Vendor_Properties
		)
	),
	

);
