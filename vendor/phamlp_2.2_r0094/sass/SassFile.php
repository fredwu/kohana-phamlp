<?php
/* SVN FILE: $Id: SassFile.php 67 2010-04-18 11:51:40Z chris.l.yates $ */
/**
 * SassFile class file.
 * File handling utilites.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass
 */

/**
 * SassFile class.
 * @package			PHamlP
 * @subpackage	Sass
 */
class SassFile {
	const CSS = '.css';
	const SASS = '.sass';
	const SASSC = '.sassc';

	/**
	 * Returns the parse tree for a file.
	 * If caching is enabled a cached version will be used if possible; if not the
	 * parsed file will be cached.
	 * @param string filename to parse
	 * @param array parse options
	 * @return SassRootNode
	 */
	static public function getTree($filename, $options) {
		if ($options['cache']) {
			$cached = self::getCachedFile($filename, $options);
			if ($cached !== false) {
				return $cached;
			}
		}

		$parser = new SassParser($options);
		$tree = $parser->parse($filename);
		if ($options['cache']) {
			self::setCachedFile($tree, $filename, $options);
		}
		return $tree;
	 }

	/**
	 * Returns the full path to a file to parse.
	 * The file is looked for recursively under the load_paths directories and
	 * the template_location directory.
	 * If the filename does not end in .sass add it.
	 * @param string filename to find
	 * @param array parse options
	 * @return string path to file
	 * @throws SassException if file not found
	 */
	static public function getFile($filename, $options) {
		if (substr($filename, -5) !== self::SASS) {
			$filename .= self::SASS;
		}

		if (file_exists($filename)) {
			return $filename;
		}
		elseif (file_exists($options['file']['dirname'] . DIRECTORY_SEPARATOR . $filename)) {
			return $options['file']['dirname'] . DIRECTORY_SEPARATOR . $filename;
		}

		foreach ($options['load_paths'] as $loadPath) {
			$path = self::findFile($filename, realpath($loadPath));
			if ($path !== false) {
				return $path;
			}
		} // foreach

		if (isset($options['template_location'])) {
			$path = self::findFile($filename, realpath($options['template_location']));
			if ($path !== false) {
				return $path;
			}
		}
		throw new SassException("Unable to find file $filename\nImported in " . join(DIRECTORY_SEPARATOR, $options['file']));
	}

	/**
	 * Looks for the file recursively in the specified directory.
	 * @param string filename to look for
	 * @param string path to directory to look in and under
	 * @return mixed string: full path to file if found, false if not
	 */
	static public function findFile($filename, $dir) {
		if (file_exists($dir . DIRECTORY_SEPARATOR . $filename)) {
			return $dir . DIRECTORY_SEPARATOR . $filename;
		}

		$files = array_slice(scandir($dir), 2);

		foreach ($files as $file) {
			if (is_dir($file)) {
				$path = self::findFile($filename, $dir . DIRECTORY_SEPARATOR . $file);
				if ($path !== false) {
					return $path;
				}
			}
		} // foreach
	  return false;
	}

	/**
	 * Returns a cached version of the file if available.
	 * @param string filename to fetch
	 * @param array parse options
	 * @return mixed the cached file if available or false if it is not
	 */
	static public function getCachedFile($filename, $options) {
		$cached = realpath($options['cache_location']) . DIRECTORY_SEPARATOR .
			md5($filename) . self::SASSC;

		if ($cached && file_exists($cached) &&
				filemtime($cached) >= filemtime($filename)) {
			return unserialize(file_get_contents($cached));
		}
		return false;
	}

	/**
	 * Saves a cached version of the file.
	 * @param SassRootNode Sass tree to save
	 * @param string filename to save
	 * @param array parse options
	 * @return mixed the cached file if available or false if it is not
	 */
	static public function setCachedFile($sassc, $filename, $options) {
		$cacheDir = realpath($options['cache_location']);

		if (!$cacheDir) {
			mkdir($options['cache_location']);
			@chmod($options['cache_location'], 0777);
			$cacheDir = realpath($options['cache_location']);
		}

		$cached = $cacheDir . DIRECTORY_SEPARATOR . md5($filename) . self::SASSC;

		return file_put_contents($cached, serialize($sassc));
	}
}