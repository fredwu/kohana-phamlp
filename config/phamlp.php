<?php defined('SYSPATH') or die('No direct script access.');
return array
(
	'phamlp' => array
	(
		'lib_dir' => realpath(dirname(__FILE__).'/../vendor/phamlp_2.2_r0094/').'/',
		'haml' => array
		(
			'cache_dir' => '_compiled/', // cache_dir is a directory within APPPATH.'views/'
			'extension' => '.haml', // HAML template file extension
		),
	),
);
