<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Module;

use Symfony\Component\HttpFoundation\Request;

/**
 * The ModuleResolverInterface defines the behaviour required to convert module names into class names, and to retrieve
 * class names for modules based on their type.
 *
 * Some types of modules are accessed as sequences, while others are "singleton" modules, of which there is only ever
 * one per resolver.
 */
interface ModuleResolverInterface {

	/**
	 * Determine the fully namespaced class name for the module with the given name.
	 *
	 * @param string $name Module to find the class name for.
	 *
	 * @return string Class name.
	 */
	public function getModuleClassName($name);

	/**
	 * Determine the name of the given module.
	 *
	 * @param string|\ReflectionClass|\Sitegear\Base\Module\ModuleInterface $module Module to get the name for.
	 *
	 * @return string Module name, of the form expected by moduleClassName().
	 *
	 * @throws \InvalidArgumentException If the argument is not a valid module instance, class name or reflected class.
	 */
	public function getModuleName($module);

	/**
	 * Get the name of the module used as a default context for rendering site content.  This module also provides the
	 * root level data for the site navigation.
	 *
	 * @return string Name of a \Sitegear\Base\Module\ModuleInterface implementation.
	 */
	public function getDefaultContentModule();

	/**
	 * Get the names of modules used to run bootstrap functionality.
	 *
	 * @return array Array of strings being names of \Sitegear\Base\Module\BootstrapModuleInterface implementations.
	 */
	public function getBootstrapModuleSequence();

}
