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

	//-- Constants --------------------

	const ENTITY_ALIAS = 'Locations';

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
		$this->getEngine()->doctrine()->getEntityManager()->getConfiguration()->addEntityNamespace(self::ENTITY_ALIAS, '\\Sitegear\\Ext\\Module\\Locations\\Model');
	}

	//-- Page Controller Methods --------------------

	public function indexController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('LocationsModule::indexController');
		$this->applyConfigToView('page.index', $view);
		$view['regions'] = $this->getRepository('Region')->findByParent(null);
		$view['title'] = $this->config('title');
		$view['root-url'] = $this->getMountedUrl();
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
		return $this->buildNavigationDataImpl($mode, intval($this->config('navigation.max-depth')));
	}

	//-- Internal Methods --------------------

	/**
	 * Recursive method for navigation generation.
	 *
	 * @param int $mode
	 * @param int $maxDepth
	 * @param null|int|\Sitegear\Ext\Module\Locations\Model\Region $parent
	 *
	 * @return array
	 */
	private function buildNavigationDataImpl($mode, $maxDepth, $parent=null) {
		$result = array();
		foreach ($this->getRepository('Region')->findByParent($parent) as $region) {
			/** @var \Sitegear\Ext\Module\Locations\Model\Region $region */
			$regionResult = array(
				'url' => sprintf('%s/%s/%s', $this->getMountedUrl(), $this->config('routes.region'), $region->getUrlPath()),
				'label' => $region->getName(),
				// TODO Make this configurable
				'tooltip' => sprintf('Find locations in "%s"', $region->getName())
			);
			if ($mode === self::NAVIGATION_DATA_MODE_EXPANDED || $maxDepth !== 1) { // >1 means more levels before the limit is reached, <=0 means no limit
				$subRegions = $this->buildNavigationDataImpl($mode, max(0, $maxDepth - 1), $region);
				if (!empty($subRegions)) {
					$regionResult['children'] = $subRegions;
				}
			}
			$result[] = $regionResult;
		}
		return $result;
	}

	/**
	 * @param string $entity
	 *
	 * @return \Doctrine\ORM\EntityRepository
	 */
	private function getRepository($entity) {
		return $this->getEngine()->doctrine()->getEntityManager()->getRepository(sprintf('%s:%s', self::ENTITY_ALIAS, $entity));
	}

}
