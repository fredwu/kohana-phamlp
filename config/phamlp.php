<?php defined('SYSPATH') or die('No direct script access.');
return array
(
	'phamlp' => array
	(
		'lib_dir' => MODPATH.'haml/vendor/phamlp_2.2_r0094/',
		'haml'    => array
		(
			'cache_dir' => 'haml/', // cache_dir is a directory within APPPATH/cache
			'extension' => '.haml',
			'options'   => array
			(
				'debug'               => 0,
				'emptyTags'           => array('meta', 'img', 'link', 'br', 'hr', 'input', 'area', 'param', 'col', 'base'),
				'filterDir'           => null,
				'format'              => 'html5',
				'inlineTags'          => array('a', 'abbr', 'accronym', 'b', 'big', 'cite', 'code', 'dfn', 'em', 'i', 'kbd', 'label', 'q', 'samp', 'small', 'span', 'strike', 'strong', 'tt', 'u', 'var'),
				'minimizedAttributes' => array('compact', 'checked', 'declare', 'readonly', 'disabled', 'selected', 'defer', 'ismap', 'nohref', 'noshade', 'nowrap', 'multiple', 'noresize'),
				'preserve'            => array('pre', 'textarea'),
				'ugly'                => true,
				'helperFile'          => null,
			)
		),
	),
);
