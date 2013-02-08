<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\ResourcesIntegration;

use Sitegear\Base\Module\AbstractUrlMountableModule;
use Sitegear\Base\View\Resources\ResourceLocations;
use Sitegear\Core\Engine\Engine;
use Sitegear\Util\LoggerRegistry;
use Sitegear\Util\NameUtilities;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

/**
 * This module handles requests for all internal resources (/sitegear/resources/*).
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class ResourcesIntegrationModule extends AbstractUrlMountableModule {

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Site Resources Integration';
	}

	//-- BootstrapModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function resourceController(Request $request) {
		LoggerRegistry::debug('ResourcesIntegrationModule::resourceController');
		$location = $request->attributes->get('location');
		$locationPath = ($location === 'engine') ? ResourceLocations::RESOURCE_LOCATION_ENGINE : ResourceLocations::RESOURCE_LOCATION_MODULE;
		$path = $this->getEngine()->getSiteInfo()->getPublicPath($locationPath, $location, $request->attributes->get('path'));
		return $this->getEngine()->createFileResponse($request, $path);
	}

	//-- AbstractUrlMountableModule Methods -------------------

	/**
	 * {@inheritDoc}
	 */
	protected function buildRoutes() {
		$routes = new RouteCollection();
		$routes->add('resource', new Route($this->getMountedUrl() . '/{location}/{path}', array(), array( 'path' => '.+' )));
		return $routes;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildNavigationData($mode) {
		return array();
	}
}
