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
 * ModuleInterface defines the behaviour of objects that provide specific functionality to the website application.
 *
 * There are several other interfaces, which are not related to ModuleInterface in the inheritance hierarchy, but are
 * implemented by modules that wish to implement specific behavioural patterns.  These Module interfaces enable
 * modules to be used in particular ways internally by Sitegear, and exposed to the user in particular ways.  See the
 * interfaces in the <code>\Sitegear\Base\Module</code> package for more details.
 */
interface ModuleInterface {

	/**
	 * Get the name of the module that should be used when referring to it in management user interface, etc.
	 *
	 * @return string
	 */
	public function getDisplayName();

	/**
	 * Start the module.  This should initialise any internal resources such as loading core data from files.
	 */
	public function start();

	/**
	 * Retrieve the engine that contains this module.
	 *
	 * @return \Sitegear\Base\Engine\EngineInterface Engine that contains this module.
	 */
	public function getEngine();

	/**
	 * Get the root file path of the module, which is the path containing the final implementation of this interface.
	 *
	 * @return string Path, always ends with a trailing slash ('/').
	 */
	public function getModuleRoot();

	/**
	 * Get a map of resources used by this module, in a form accepted by ResourcesManagerInterface::registerMap().
	 *
	 * @return array[]
	 */
	public function getResourceMap();

}
