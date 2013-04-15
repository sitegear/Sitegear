<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Mock;

use Sitegear\Config\FileLoader\FileLoaderInterface;

/**
 * Mock FileLoader interface implementation, used only for testing registerFileLoader() / unregisterFileLoader().
 */
class MockFileLoader implements FileLoaderInterface {

	public function supports($args) {
		return true;
	}

	public function load($args) {
		return array();
	}
}
