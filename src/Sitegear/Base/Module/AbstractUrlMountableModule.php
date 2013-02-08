<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Module;

use Sitegear\Base\Module\AbstractConfigurableModule;
use Sitegear\Base\Module\MountableModuleInterface;
use Sitegear\Base\Engine\EngineInterface;

/**
 * Extends AbstractConfigurableModule by providing the basic mounting functionality required by MountableModuleInterface.
 * The route and navigation data generation is left to the sub-class.
 */
abstract class AbstractUrlMountableModule extends AbstractConfigurableModule implements MountableModuleInterface {

	//-- Attributes --------------------

	/**
	 * @var string|null
	 */
	private $mountedUrl;

	/**
	 * @var \Symfony\Component\Routing\RouteCollection|null
	 */
	private $routes;

	/**
	 * @var array[]
	 */
	private $navigationData;

	//-- Constructor --------------------

	public function __construct(EngineInterface $engine) {
		parent::__construct($engine);
		$this->mountedUrl = null;
		$this->routes = null;
		$this->navigationData = array();
	}

	//-- MountableModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function mount($mountedUrl=null) {
		$this->mountedUrl = trim($mountedUrl, '/');
	}

	/**
	 * {@inheritDoc}
	 */
	public function unmount() {
		$this->mountedUrl = null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMountedUrl() {
		return $this->mountedUrl;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRoutes() {
		if ($this->routes === null) {
			$this->routes = $this->buildRoutes();
		}
		return $this->routes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getNavigationData($mode) {
		if (!isset($this->navigationData[$mode])) {
			$this->navigationData[$mode] = $this->buildNavigationData($mode);
		}
		return $this->navigationData[$mode];
	}

	//-- Internal Methods --------------------

	/**
	 * Build the getRoutes collection for this module.  Called once during mount() so that getRoutes can be reused.
	 *
	 * @return \Symfony\Component\Routing\RouteCollection
	 */
	protected abstract function buildRoutes();

	/**
	 * Build the navigation data for this module.  Called once during mount() so that navigation data can be reused.
	 *
	 * @param integer $mode One of the NAVIGATION_DATA_MODE_* constants.
	 *
	 * @return array
	 */
	protected abstract function buildNavigationData($mode);

}
