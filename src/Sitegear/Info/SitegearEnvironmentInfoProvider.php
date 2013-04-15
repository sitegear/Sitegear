<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Info;

use Sitegear\Info\EnvironmentInfoProviderInterface;

/**
 * Default implementation of EnvironmentInfoProviderInterface, which sets "dev mode" if the environment is "dev",
 * "development" or unset (null).
 */
class SitegearEnvironmentInfoProvider implements EnvironmentInfoProviderInterface {

	//-- Attributes --------------------

	/**
	 * @var string|null Application environment setting, usually either "development", "test", "staging" or
	 *   "production", or null for not set.
	 */
	private $environment;

	//-- Constructor --------------------

	/**
	 * @param string|null $environment
	 */
	public function __construct($environment=null) {
		$this->environment = $environment;
	}

	//-- EnvironmentInfoProviderInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getEnvironment() {
		return $this->environment;
	}

	/**
	 * @inheritdoc
	 */
	public function isDevMode() {
		return empty($this->environment) || in_array($this->environment, array( 'development', 'dev' ));
	}

}
