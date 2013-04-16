<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module;

use Sitegear\Module\AbstractConfigurableModule;
use Sitegear\Module\MountableModuleInterface;
use Sitegear\Util\LoggerRegistry;

use Sitegear\Util\TypeUtilities;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Generator\UrlGenerator;

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
	private $navigationData = array();

	//-- MountableModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function mount($mountedUrl=null, RequestContext $context) {
		LoggerRegistry::debug('{class}::mount({mountedUrl})', array( 'class' => (new \ReflectionClass($this))->getShortName(), 'mountedUrl' => TypeUtilities::describe($mountedUrl) ));
		$this->mountedUrl = trim($mountedUrl, '/');
		$this->routes = $this->buildRoutes();
	}

	/**
	 * @inheritdoc
	 */
	public function unmount() {
		LoggerRegistry::debug('{class}::unmount()', array( 'class' => (new \ReflectionClass($this))->getShortName() ));
		$this->mountedUrl = null;
	}

	/**
	 * @inheritdoc
	 */
	public function getMountedUrl() {
		return $this->mountedUrl;
	}

	/**
	 * @inheritdoc
	 */
	public function getRoutes() {
		return $this->routes;
	}

	/**
	 * @inheritdoc
	 */
	public function getNavigationData($mode) {
		if (!isset($this->navigationData[$mode])) {
			$this->navigationData[$mode] = $this->buildNavigationData($mode);
		}
		return $this->navigationData[$mode];
	}

	//-- Internal Methods --------------------

	/**
	 * Build the routes for this module.  This is cached so that this method is only called once per request.
	 *
	 * @return RouteCollection
	 */
	protected abstract function buildRoutes();

	/**
	 * Build the navigation data for this module.  Called once during mount() so that navigation data can be reused.
	 * This method should be overridden by any module wishing to provide navigation data.
	 *
	 * @param integer $mode One of the NAVIGATION_DATA_MODE_* constants.
	 *
	 * @return array
	 */
	protected abstract function buildNavigationData($mode);

}
