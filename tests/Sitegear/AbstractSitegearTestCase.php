<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear;

/**
 * Extends the base test case class by adding a fixtures() method, which returns the path to the fixtures directory,
 * which contains resources used for testing.
 */
abstract class AbstractSitegearTestCase extends \PHPUnit_Framework_TestCase {

	//-- Constants --------------------

	/**
	 * Name of the fixtures directory.
	 */
	const DIR_FIXTURES = 'fixtures';

	//-- Internal Helper Methods --------------------

	/**
	 * Get the path to the fixtures directory, which contains resources used for testing.  This is done by inspecting
	 * the namespace of the test class, and assuming that every namespace is a directory level, also that the fixtures
	 * directory is a sibling to the top-level code directory.
	 *
	 * @return string Absolute directory path, with a trailing slash for convenience.
	 */
	protected function fixtures() {
		$class = new \ReflectionClass($this);
		$namespace = $class->getNamespaceName();
		$dir = __DIR__;
		foreach (explode($namespace, '\\') as $n) {
			$dir = dirname($dir);
		}
		return $dir . '/' . self::DIR_FIXTURES . '/';
	}

}
