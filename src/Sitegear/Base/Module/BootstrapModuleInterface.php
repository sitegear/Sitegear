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
 * Defines the behaviour of a Module that is involved in the Sitegear bootstrap.
 *
 * Bootstrap modules are attached to the KernelEvents::REQUEST event, and therefore have the ability to shorten the
 * processing cycle by returning a Response object from the runBootstrap() method.  When this occurs, no controller
 * method is called, and no rendering occurs (except decoration, see below).
 */
interface BootstrapModuleInterface {

	/**
	 * Initialise the module during the Sitegear bootstrap.
	 *
	 * Normally, this method will return null (or not return anything, which in PHP is the same), which means the
	 * bootstrap sequence should continue.  However if any bootstrap module returns a Response from this method, it
	 * should be considered the final response for the request, and no more processing (including the main rendering
	 * cycle) will be performed, except for decoration.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return void|null|\Symfony\Component\HttpFoundation\Response If a Response is returned, then it will take
	 *   precedence over any further bootstrap processing and rendering, other than decoration.
	 */
	public function bootstrap(Request $request);

}
