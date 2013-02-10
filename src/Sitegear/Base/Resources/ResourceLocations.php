<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Resources;

/**
 * Defines the locations that a resource may exist in.  These locations can be searched one at a time for a given
 * resource, in order to retrieve a site-scope resource in preference to a module-scope resource, in preference to an
 * engine-scope resource.
 */
final class ResourceLocations {

	/**
	 * Specifies that a resource is site-specific.
	 */
	const RESOURCE_LOCATION_SITE = 'site';

	/**
	 * Specifies that a resource is in the engine scope.
	 */
	const RESOURCE_LOCATION_ENGINE = 'engine';

	/**
	 * Specifies that a resource is in the scope of a particular module (the module must be specified or contextual).
	 */
	const RESOURCE_LOCATION_MODULE = 'module';

	/**
	 * Name of the directory used to store resources that are internal to the module.
	 */
	const RESOURCES_DIRECTORY = 'Resources';

}
