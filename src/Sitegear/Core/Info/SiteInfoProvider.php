<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Info;

use Sitegear\Base\Info\SiteInfoProviderInterface;
use Sitegear\Base\View\Resources\ResourceLocations;
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
		$this->siteRoot = trim(rtrim($siteRoot, '/')) . '/';
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
	public function getAdministrator() {
		return $this->engine->config('site.administrator');
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
			case ResourceLocations::RESOURCE_LOCATION_MODULE:
				$root = is_string($module) ? $this->engine->getModule($module)->getModuleRoot() : $module->getModuleRoot();
				break;
			case ResourceLocations::RESOURCE_LOCATION_ENGINE:
				$root = $this->engine->getEngineRoot();
				break;
			default:
				throw new \InvalidArgumentException(sprintf('Cannot find site resource in unknown location "%s"', $location));
		}
		// Ensure the configured site path ends with a single trailing slash and return the result
		$site = trim($this->engine->config('paths.site'), '/') . '/';
		return $root . $site . $resource;
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
			case ResourceLocations::RESOURCE_LOCATION_MODULE:
				$root = $this->engine->getModule($module)->getModuleRoot();
				break;
			case ResourceLocations::RESOURCE_LOCATION_ENGINE:
				$root = $this->engine->getEngineRoot();
				break;
			default:
				throw new \InvalidArgumentException(sprintf('Cannot find public resource in unknown location "%s"', $location));
		}
		// Ensure the configured public path ends with a single trailing slash and return the result
		$public = trim($this->engine->config('paths.public'), '/') . '/';
		return $root . $public . $resource;
	}

}
