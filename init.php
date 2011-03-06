<?php

$config = Kohana::config('phamlp');
if ( in_array('haml', $config['parsers']) && ! class_exists('HamlParser'))
{
	$haml_dir = $config['lib_dir']. '/haml';
	
	if (!$haml_dir)
	{
		throw new Exception("Cannot find phamlp's HAML directory.");
	}

	require_once(Kohana::find_file('vendor', $haml_dir.'/HamlParser'));
}

if ( in_array('sass', $config['parsers']) && ! class_exists('SassParser'))
{
	$sass_dir = $config['lib_dir']. '/sass';
	
	if (!$sass_dir)
	{
		throw new Exception("Cannot find phamlp's SASS directory.");
	}

	require_once(Kohana::find_file('vendor', $sass_dir.'/SassParser'));
}


