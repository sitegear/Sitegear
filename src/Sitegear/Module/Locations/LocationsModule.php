<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Locations;

use Sitegear\Info\ResourceLocations;
use Sitegear\Util\TypeUtilities;
use Sitegear\View\ViewInterface;
use Sitegear\Module\AbstractSitegearModule;
use Sitegear\Module\Locations\Repository\ItemRepository;
use Sitegear\Util\StringUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Doctrine\ORM\NoResultException;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * LocationsModule allows display and management of geographical locations.  By default, it requires GoogleModule for
 * map generation, however this can be overridden using custom view scripts.
 *
 * @method \Sitegear\Engine\SitegearEngine getEngine()
 */
class LocationsModule extends AbstractSitegearModule {

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getDisplayName() {
		return 'Locations Management';
	}

	/**
	 * @inheritdoc
	 */
	public function start() {
		parent::start();
		// Register "location-search" form.
		$filename = $this->config('location-search-form.filename');
		$this->getEngine()->forms()->registry()->registerFormDefinitionFilePath($this->config('location-search-form.key'), $this, $filename);
	}

	//-- AbstractUrlMountableModule Methods --------------------

	/**
	 * @inheritdoc
	 */
	protected function buildNavigationData($mode) {
		$data = $this->buildNavigationDataImpl($mode, intval($this->config('navigation.max-depth')));
		if ($mode === self::NAVIGATION_DATA_MODE_EXPANDED) {
			foreach ($this->getRepository('Item')->findByActive(true) as $item) {
				/** @var \Sitegear\Module\Locations\Model\Item $item */
				$data[] = array(
					'url' => $this->getRouteUrl('item', $item->getUrlPath()),
					'label' => $item->getName()
				);
			}
		}
		return $data;
	}

	//-- Page Controller Methods --------------------

	/**
	 * Display the top-level landing page for the locations module.
	 *
	 * @param \Sitegear\View\ViewInterface $view
	 */
	public function indexController(ViewInterface $view) {
		LoggerRegistry::debug('LocationsModule::indexController()');
		$view['regions'] = new ArrayCollection($this->getRepository('Region')->findByParent(null));
	}

	/**
	 * Display a landing page for a region.
	 *
	 * @param \Sitegear\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function regionController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('LocationsModule::regionController()');
		/** @var \Sitegear\Module\Locations\Model\Region $region */
		$region = $this->getRepository('Region')->findOneByUrlPath($request->attributes->get('slug'));
		$view['region'] = $region;
		$view['regions'] = $region->getChildren();
		$view['items'] = $region->getItems();
	}

	/**
	 * Display the details page for an individual location item.
	 *
	 * @param \Sitegear\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	public function itemController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('LocationsModule::itemController()');
		try {
			$view['item'] = $this->getRepository('Item')->findOneBy(array( 'urlPath' => $request->attributes->get('slug'), 'active' => true ));
		} catch (NoResultException $e) {
			throw new NotFoundHttpException('The requested product is not available.', $e);
		}
	}

	/**
	 * Perform a search for a given (named) location and display results within a specified radius.
	 *
	 * TODO Another parameter, region, to restrict results to that region (and its children)
	 *
	 * @param \Sitegear\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return RedirectResponse|null
	 *
	 * @throws \InvalidArgumentException
	 */
	public function searchController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('LocationsModule::itemController()');
		$query = $request->query->get('query');
		$radius = $request->query->get('radius');
		if (strlen($query) === 0) {
			return new RedirectResponse($this->getRouteUrl('index'));
		}
		if (!is_numeric($radius)) {
			throw new \InvalidArgumentException(sprintf('LocationsModule received invalid radius in search; must be numeric, "%s" received', $radius));
		}
		$view['query'] = $query = StringUtilities::replaceTokens($this->config('search.query-mask'), array( 'query' => $query ));
		$view['radius'] = $radius = intval($radius);
		$location = $this->getEngine()->google()->geocodeLocation($query);
		/** @var ItemRepository $itemRepository */
		$itemRepository = $this->getRepository('Item');
		$view['items'] = new ArrayCollection($radius > 0 ? $itemRepository->findInRadius($location, $radius) : array());
		$view['results-description'] = StringUtilities::replaceTokens($view['results-description-format'], array( 'query' => $query, 'radius' => number_format($radius) ));
		$view['no-items'] = 'no-items-search';
		return null;
	}

	//-- Component Controller Methods --------------------

	/**
	 * The location search form.
	 *
	 * @param ViewInterface $view
	 * @param string|null $query Previous query, to populate the value.
	 * @param string|null $radius Previous radius selection, to populate the selected option.
	 */
	public function searchFormComponent(ViewInterface $view, $query=null, $radius=null) {
		LoggerRegistry::debug('LocationsModule::searchFormComponent([view], {query}, {radius})', array( 'query' => TypeUtilities::describe($query), 'radius' => TypeUtilities::describe($radius) ));
		$view['action-url'] = $this->getRouteUrl('search');
		if (!is_null($query)) {
			$view['query'] = $query;
		}
		if (!is_null($radius)) {
			$view['radius'] = $radius;
		}
	}

	//-- Internal Methods --------------------

	/**
	 * Recursive method for navigation generation.
	 *
	 * @param int $mode
	 * @param int $maxDepth
	 * @param null|int|\Sitegear\Module\Locations\Model\Region $parent
	 *
	 * @return array
	 */
	private function buildNavigationDataImpl($mode, $maxDepth, $parent=null) {
		$result = array();
		foreach ($this->getRepository('Region')->findByParent($parent) as $region) {
			/** @var \Sitegear\Module\Locations\Model\Region $region */
			$tooltip = StringUtilities::replaceTokens(
				$this->config('navigation.tooltip-format'),
				array(
					'regionName' => $region->getName()
				)
			);
			$regionResult = array(
				'url' => $this->getRouteUrl('region', $region->getUrlPath()),
				'label' => $region->getName(),
				'tooltip' => $tooltip
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

}
