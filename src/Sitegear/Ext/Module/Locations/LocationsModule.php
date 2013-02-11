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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Doctrine\ORM\NoResultException;

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

	/**
	 * Display the top-level landing page for the locations module.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 */
	public function indexController(ViewInterface $view) {
		LoggerRegistry::debug('LocationsModule::indexController');
		$this->applyViewDefaults($view);
		$this->applyConfigToView('page.index', $view);
		$view['regions'] = $this->getRepository('Region')->findByParent(null);
	}

	/**
	 * Display a landing page for a region.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function regionController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('LocationsModule::regionController');
		$this->applyViewDefaults($view);
		$this->applyConfigToView('page.region', $view);
		/** @var \Sitegear\Ext\Module\Locations\Model\Region $region */
		$region = $this->getRepository('Region')->findOneByUrlPath($request->attributes->get('slug'));
		$view['region'] = $region;
		$view['regions'] = $region->getChildren();
		$view['items'] = $region->getItems();
	}

	/**
	 * Display the details page for an individual location item.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	public function itemController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('LocationsModule::itemController');
		$this->applyViewDefaults($view);
		$this->applyConfigToView('page.item', $view);
		try {
			$view['item'] = $this->getRepository('Item')->findOneBy(array( 'urlPath' => $request->attributes->get('slug'), 'active' => true ));
		} catch (NoResultException $e) {
			throw new NotFoundHttpException('The requested product is not available.', $e);
		}
	}

	/**
	 * Perform a search for a given (named) location and display results within a specified radius.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function searchController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('LocationsModule::itemController');
		$this->applyViewDefaults($view);
		$this->applyConfigToView('page.search', $view);
		// TODO Another parameter, region, to restrict results to that region (and its children)
		$query = sprintf($this->config('search.query-mask'), $request->query->get('query', null));
		$location = $this->getEngine()->google()->geocodeLocation($query);
		$radius = $request->query->get('radius', $this->getDefaultRadius());
		$view['items'] = $this->getRepository('Item')->findInRadius($location, $radius);
	}

	//-- Component Controller Methods --------------------

	/**
	 * The location search form.
	 *
	 * @param ViewInterface $view
	 * @param Request $request
	 * @param string|null $query Previous query, to populate the value.
	 * @param string|null $radius Previous radius selection, to populate the selected option.
	 */
	public function searchFormComponent(ViewInterface $view, Request $request, $query=null, $radius=null) {
		$this->applyViewDefaults($view);
		$this->applyConfigToView('component.search-form', $view);
		$view['action-url'] = sprintf('%s/search', $this->getMountedUrl());
		$view['query'] = $query;
		$view['radius'] = $radius;
	}

	//-- AbstractUrlMountableModule Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function buildRoutes() {
		$routes = new RouteCollection();
		$routes->add('index', new Route($this->getMountedUrl()));
		$routes->add('region', new Route(sprintf('%s/%s/{slug}', $this->getMountedUrl(), $this->config('routes.region'))));
		$routes->add('item', new Route(sprintf('%s/%s/{slug}', $this->getMountedUrl(), $this->config('routes.item'))));
		$routes->add('search', new Route(sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.search'))));
		return $routes;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildNavigationData($mode) {
		$data = $this->buildNavigationDataImpl($mode, intval($this->config('navigation.max-depth')));
		if ($mode === self::NAVIGATION_DATA_MODE_EXPANDED) {
			foreach ($this->getRepository('Item')->findByActive(true) as $item) {
				/** @var \Sitegear\Ext\Module\Locations\Model\Item $item */
				$data[] = array(
					'url' => sprintf('%s/%s/%s', $this->getMountedUrl(), $this->config('routes.item'), $item->getUrlPath()),
					'label' => $item->getName()
				);
			}
		}
		return $data;
	}

	//-- Internal Methods --------------------

	/**
	 * Apply view defaults that are used by all pages in the locations module.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 */
	private function applyViewDefaults(ViewInterface $view) {
		$view['title'] = $this->config('title');
		$view['region-path'] = trim($this->config('region-path'), '/');
		$view['item-path'] = trim($this->config('item-path'), '/');
		$view['item-url'] = $this->config('routes.item');
		$view['region-url'] = $this->config('routes.region');
		$view['root-url'] = $this->getMountedUrl();
	}

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
	 * @return int|null Get the default radius as configured.
	 */
	private function getDefaultRadius() {
		$result = null;
		foreach ($this->config('component.search-form.radius-options') as $option) {
			if (isset($option['default']) && $option['default']) {
				$result = $option['value'];
			}
		}
		return $result ?: $this->config('component.search-form.radius-options.0.value');
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
