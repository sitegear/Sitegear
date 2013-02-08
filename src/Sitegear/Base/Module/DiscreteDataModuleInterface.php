<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Module;

/**
 * Defines the behaviour of a module that provides "atomic" access to some data source.  That is, it accepts a string
 * "selector", which uniquely identifies any available *individual* piece of data -- i.e. a string, number, boolean or
 * date/time value, but not an array, object, or other complex structure.
 *
 * The syntax of the selector will depend on the implementation.
 *
 * This exists primarily for the purpose of the content editor widget, so that it has a unified method of loading and
 * saving data from and to any number of sources.
 */
interface DiscreteDataModuleInterface {

	/**
	 * Load a single piece of data.
	 *
	 * @param string $selector Selector specifying where to load the data from.
	 *
	 * @return null|string|number|boolean Loaded value, or null if no such data exists.
	 *
	 * @throws \InvalidArgumentException If the selector does not have the correct syntax.
	 */
	public function load($selector);

	/**
	 * Save a single piece of data.
	 *
	 * @param string $selector Selector specifying where to save the data.
	 * @param string|number|boolean $value Data to be saved.
	 *
	 * @return boolean Success flag.
	 *
	 * @throws \InvalidArgumentException If the selector does not have the correct syntax.
	 */
	public function save($selector, $value);

	/**
	 * Upload file(s) as multipart form data and save the file data through this adapter using the given selector as
	 * the parent location, and the given filenames.
	 *
	 * @param mixed $selector String or object defining the selection of data to update, according to the type of
	 *   Adapter.
	 *
	 * @throws \BadMethodCallException If the upload action isn't supported by the module.
	 */
	public function upload($selector);

}
