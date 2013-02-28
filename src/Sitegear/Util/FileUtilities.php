<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

/**
 * Generic file related utilities.
 */
class FileUtilities {

	/**
	 * Retrieve the first path from the given array which exists, or null if none exist.
	 *
	 * @param string[] $paths
	 *
	 * @return string|null
	 */
	public static function firstExistingPath(array $paths) {
		$filtered = array_filter($paths, function($path) {
			return file_exists($path);
		});
		// `array_filter()` preserves keys, so we need to use `array_values()` here to ensure it starts from zero.
		return !empty($filtered) ? array_values($filtered)[0] : null;
	}

}
