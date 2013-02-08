<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

// Load the autoload from the vendor/ directory or from the parent project.
if (file_exists($loader = dirname(__DIR__) . '/vendor/autoload.php')) {
	/** @noinspection PhpIncludeInspection */
	$loader = require_once $loader;
} elseif (file_exists($loader = dirname(dirname(dirname(__DIR__))) . '/autoload.php')) {
	/** @noinspection PhpIncludeInspection */
	$loader = require_once $loader;
}

$loader->add('Sitegear\\', __DIR__);
