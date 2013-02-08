<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Info;

/**
 * Defines the behaviour of an object that provides information about the deployment environment, i.e. the name of the
 * environment and whether or not it is considered a "dev mode" environment.
 */
interface EnvironmentInfoProviderInterface {

	/**
	 * Get the application environment setting, for example "development" or "production".
	 *
	 * @return string|null Application environment, or null if it is not set.
	 */
	public function getEnvironment();

	/**
	 * Determine whether the application's environment setting is considered a "dev mode" environment.  This triggers
	 * certain behaviours that should only occur in development.
	 *
	 * @return boolean True only if the application environment is considered to be in "dev mode".
	 */
	public function isDevMode();

}
