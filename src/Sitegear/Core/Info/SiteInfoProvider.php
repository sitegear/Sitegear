<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Info;

use Sitegear\Base\Info\SiteInfoProviderInterface;
use Sitegear\Base\Resources\ResourceLocations;
use Sitegear\Core\Engine\Engine;
use Sitegear\Util\NameUtilities;

/**
 * Implementation of SiteInfoProviderInterface coupled with the core Engine implementation.
 */
class SiteInfoProvider implements SiteInfoProviderInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Core\Engine\Engine
	 */
	private $engine;

	/**
	 * @var string
	 */
	private $siteRoot;

	//-- Constructor --------------------

	public function __construct(Engine $engine, $siteRoot) {
		$this->engine = $engine;
		$this->siteRoot = trim(rtrim($siteRoot, '/'));
	}

	//-- SiteInfoProviderInterface --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getIdentifier() {
		return $this->engine->config('site.id');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return $this->engine->config('site.display-name');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLogoUrl() {
		return $this->engine->config('site.logo-url');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAdministratorName() {
		return $this->engine->config('site.administrator.name');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAdministratorEmail() {
		return $this->engine->config('site.administrator.email-address');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSiteEmail($key) {
		return $this->engine->config('site.email.' . $key);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSiteRoot() {
		return $this->siteRoot;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSitePath($location, $module, $resource) {
		// Ensure the passed-in resource has no leading slashes and no surrounding whitespace
		$resource = ltrim(trim($resource), '/');
		switch ($location) {
			case ResourceLocations::RESOURCE_LOCATION_SITE:
				$root = $this->getSiteRoot();
				if (!is_string($module)) {
					$module = NameUtilities::convertToDashedLower($this->engine->getModuleName($module));
				}
				// For the site level, the module name must be inserted
				$resource = $module . '/' . $resource;
				break;
			case ResourceLocations::RESOURCE_LOCATION_VENDOR:
				throw new \InvalidArgumentException('The "vendor" location identifier cannot be used for site paths');
			case ResourceLocations::RESOURCE_LOCATION_MODULE:
				if (is_string($module)) {
					$module = $this->engine->getModule($module);
				}
				$root = sprintf('%s/%s', $module->getModuleRoot(), ResourceLocations::RESOURCES_DIRECTORY);
				break;
			case ResourceLocations::RESOURCE_LOCATION_ENGINE:
				$root = sprintf('%s/%s', $this->engine->getEngineRoot(), ResourceLocations::RESOURCES_DIRECTORY);
				break;
			default:
				throw new \InvalidArgumentException(sprintf('Cannot find site resource in unknown location "%s"', $location));
		}
		return sprintf('%s/site/%s', $root, $resource);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPublicPath($location, $module, $resource) {
		// Get the name of the module
		if (!is_string($module) || strpos('\\', $module) !== false) {
			$module = $this->engine->getModuleName($module);
		}
		// Ensure the passed-in resource has no leading slashes and no surrounding whitespace
		$resource = ltrim(trim($resource), '/');
		switch ($location) {
			case ResourceLocations::RESOURCE_LOCATION_SITE:
				$root = $this->getSiteRoot();
				break;
			case ResourceLocations::RESOURCE_LOCATION_VENDOR:
				$root = $this->engine->getSitegearInfo()->getSitegearRoot();
				break;
			case ResourceLocations::RESOURCE_LOCATION_MODULE:
				$root = sprintf('%s/%s', $this->engine->getModule($module)->getModuleRoot(), ResourceLocations::RESOURCES_DIRECTORY);
				break;
			case ResourceLocations::RESOURCE_LOCATION_ENGINE:
				$root = sprintf('%s/%s', $this->engine->getEngineRoot(), ResourceLocations::RESOURCES_DIRECTORY);
				break;
			default:
				throw new \InvalidArgumentException(sprintf('Cannot find public resource in unknown location "%s"', $location));
		}
		// Ensure the configured public path ends with a single trailing slash and return the result
		return sprintf('%s/public/%s', $root, $resource);
	}

}
