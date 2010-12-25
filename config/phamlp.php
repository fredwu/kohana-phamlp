<?php defined('SYSPATH') or die('No direct script access.');
return array
(
	'phamlp' => array
	(
		'lib_dir' => realpath(dirname(__FILE__).'/../vendor/phamlp_2.2_r0094/').'/',
		'haml'    => array
		(
			'cache_dir' => 'haml/', // cache_dir is a directory within APPPATH.'cache/'
			'extension' => '.haml', // HAML template file extension
			'options'   => array
			(
				'attrWrapper'         => '"',
				'debug'               => 0,
				'doctype'             => null,
				'emptyTags'           => array('meta', 'img', 'link', 'br', 'hr', 'input', 'area', 'param', 'col', 'base'),
				'escapeHtml'          => false,
				'filterDir'           => null,
				'format'              => 'xhtml',
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
	),
);
