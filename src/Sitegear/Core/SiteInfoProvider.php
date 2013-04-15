<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core;

use Sitegear\Info\SiteInfoProviderInterface;
use Sitegear\Module\ModuleInterface;
use Sitegear\Resources\ResourceLocations;
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
	 * @inheritdoc
	 */
	public function getIdentifier() {
		return $this->engine->config('site.id');
	}

	/**
	 * @inheritdoc
	 */
	public function getDisplayName() {
		return $this->engine->config('site.display-name');
	}

	/**
	 * @inheritdoc
	 */
	public function getLogoUrl() {
		return $this->engine->config('site.logo-url');
	}

	/**
	 * @inheritdoc
	 */
	public function getAdministratorName() {
		return $this->engine->config('site.administrator.name');
	}

	/**
	 * @inheritdoc
	 */
	public function getAdministratorEmail() {
		return $this->engine->config('site.administrator.email');
	}

	/**
	 * @inheritdoc
	 */
	public function getSiteEmail($key) {
		return $this->engine->config('site.email.' . $key);
	}

	/**
	 * @inheritdoc
	 */
	public function getSiteRoot() {
		return $this->siteRoot;
	}

	/**
	 * @inheritdoc
	 */
	public function getSitePath($location, $resource, ModuleInterface $module=null) {
		// Ensure the passed-in resource has no leading slashes and no surrounding whitespace
		$resource = ltrim(trim($resource), '/');
		switch ($location) {
			case ResourceLocations::RESOURCE_LOCATION_SITE:
				// For the site level, the module name must be inserted
				$root = $this->getSiteRoot();
				$resource = sprintf('%s/%s', NameUtilities::convertToDashedLower($this->engine->getModuleName($module)), $resource);
				break;
			case ResourceLocations::RESOURCE_LOCATION_VENDOR:
				throw new \InvalidArgumentException('The "vendor" location identifier cannot be used for site paths');
			case ResourceLocations::RESOURCE_LOCATION_MODULE:
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
	 * @inheritdoc
	 */
	public function getPublicPath($location, $resource, ModuleInterface $module=null) {
		// Ensure the passed-in resource has no leading slashes and no surrounding whitespace
		$resource = ltrim(trim($resource), '/');
		switch ($location) {
			case ResourceLocations::RESOURCE_LOCATION_SITE:
				$root = $this->getSiteRoot();
				break;
			case ResourceLocations::RESOURCE_LOCATION_VENDOR:
				$root = $this->engine->getSitegearInfo()->getSitegearVendorResourcesRoot();
				break;
			case ResourceLocations::RESOURCE_LOCATION_MODULE:
				$root = sprintf('%s/%s', $module->getModuleRoot(), ResourceLocations::RESOURCES_DIRECTORY);
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
