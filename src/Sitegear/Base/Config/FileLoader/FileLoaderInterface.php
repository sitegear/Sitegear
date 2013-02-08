<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Config\FileLoader;

/**
 * Defines the behaviour of a class that is responsible for loading configuration data from a particular type of file.
 */
interface FileLoaderInterface {

	/**
	 * Determine whether this loader supports the given file.
	 *
	 * @param mixed $args File to check.
	 *
	 * @return boolean True if this loader can load it, otherwise false.
	 */
	public function supports($args);

	/**
	 * Load the given file.
	 *
	 * @param mixed $args Specifies where to load the data from.  As different loaders will have different requirements
	 *   for specifying the location of the configuration data, there are no restrictions on the type of this argument.
	 *
	 * @return array Configuration recursive array structure.
	 *
	 * @throws \InvalidArgumentException If called for a $filename that returns false from the supports() method.
	 */
	public function load($args);

}
