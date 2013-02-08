<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Module;

/**
 * The ModuleContainerInterface defines the behaviour of an object that creates and stores modules, and allows access
 * to those modules by name.
 */
interface ModuleContainerInterface {

	/**
	 * Determine if a module with the given name is available.  Note that this does not guarantee that the module can
	 * be loaded and instantiated, only that it is known by the container.
	 *
	 * @param string $name Name to check.  May be in any form understood by the NameTools utility, i.e. "camelCase",
	 *   "StudyCaps", "lower case", etc.
	 *
	 * @return boolean Whether or not the module exists.
	 */
	public function hasModule($name);

	/**
	 * Retrieve the module with the given name.  If the module is not already instantiated, but is available, it is
	 * created before being returned.  If the module is not available, or is not the correct type, an exception is
	 * thrown.
	 *
	 * @param string $name Name of the module to retrieve. May be in any form understood by the NameTools utility, i.e.
	 *   "camelCase", "StudyCaps", "lower case", etc.
	 *
	 * @return \Sitegear\Base\Module\ModuleInterface Module instance.
	 *
	 * @throws \InvalidArgumentException If the named module does not exist.
	 * @throws \DomainException If the named module does not implement ModuleInterface.
	 */
	public function getModule($name);

}
