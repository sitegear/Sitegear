<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Sitegear unit test bootstrap.
 */
return call_user_func(function() {

	// Autoloader paths to try.  The last path here is totally non-portable but works in my dev environment so meh.
	$paths = array(
		dirname(__DIR__) . '/vendor/autoload.php',
		dirname(dirname(dirname(__DIR__))) . '/autoload.php',
		dirname(dirname(dirname(__DIR__))) . '/workspace/testdrive.sitegear.org/vendor/autoload.ph'
	);

	// Get the loader from the first valid path.
	$loader = null;
	while (!empty($paths) && is_null($loader)) {
		$path = array_shift($paths);
		if (file_exists($path)) {
			/** @noinspection PhpIncludeInspection */
			$loader = require_once $path;
		}
	}

	// Stop now if we don't have a valid loader.
	if (is_null($loader)) {
		error_log(sprintf('*** FATAL ERROR: Could not find autoloader in unit test bootstrap.  Try adding a valid path to the array in %s ***', __FILE__));
		exit;
	}

	// Add the Sitegear prefix for test classes.
	$loader->add('Sitegear\\', __DIR__);

});
