<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Locations;

use Sitegear\Base\Module\AbstractUrlMountableModule;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * LocationsModule allows display and management of geographical locations.  By default, it requires GoogleModule for
 * map generation, however this can be overridden using custom view scripts.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class LocationsModule extends AbstractUrlMountableModule {

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Locations Management';
	}

	/**
	 * {@inheritDoc}
	 */
	public function start() {
		$this->getEngine()->doctrine()->getEntityManager()->getConfiguration()->addEntityNamespace('Locations', '\\Sitegear\\Ext\\Module\\Locations\\Model');
	}

	//-- Page Controller Methods --------------------

	public function indexController(ViewInterface $view, Request $request) {
		// TODO
	}

	public function itemController(ViewInterface $view, Request $request) {
		// TODO
	}

	//-- AbstractUrlMountableModule Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function buildRoutes() {
		$routes = new RouteCollection();
		$routes->add('index', new Route($this->getMountedUrl()));
		$routes->add('item', new Route(sprintf('%s/{slug}', $this->getMountedUrl())));
		return $routes;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildNavigationData($mode) {
		// TODO Build navigation data
		return array();
	}

	//-- Internal Methods --------------------

	/**
	 * @return \Sitegear\Ext\Module\Locations\Repository\ItemRepository
	 */
	protected function getItemRepository() {
		return $this->getEngine()->doctrine()->getEntityManager()->getRepository('Locations:Item');
	}

}
