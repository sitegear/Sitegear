<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Products;

use Sitegear\Base\Module\AbstractUrlMountableModule;
use Sitegear\Util\LoggerRegistry;
use Sitegear\Base\View\ViewInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

use Doctrine\ORM\NoResultException;

/**
 * Displays and allows management of products in a product category hierarchy.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class ProductsModule extends AbstractUrlMountableModule {

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Products Catalogue';
	}

	/**
	 * {@inheritDoc}
	 */
	public function start() {
		LoggerRegistry::debug('ProductsModule starting');
		$this->getEngine()->doctrine()->getEntityManager()->getConfiguration()->addEntityNamespace('Products', '\\Sitegear\\Ext\\Module\\Products\\Model');
	}

	//-- Page Controller Methods --------------------

	/**
	 * Display the main shop entrance page.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 */
	public function indexController(ViewInterface $view) {
		LoggerRegistry::debug('ProductsModule::indexController');
		$this->applyViewDefaults($view);
		$this->applyConfigToView('page.index', $view);
		$view['categories'] = $this->getCategoryRepository()->getRootCategories();
	}

	/**
	 * Display a landing page for a category.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function categoryController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('ProductsModule::categoryController');
		$this->applyViewDefaults($view);
		$this->applyConfigToView('page.category', $view);
		$view['category'] = $this->getCategoryRepository()->selectCategoryByUrlPath($request->attributes->get('slug'));
		$view['categories'] = $this->getCategoryRepository()->getChildCategories($view['category']);
		$view['items'] = $this->getItemRepository()->getActiveItemsInCategory($view['category']);
	}

	/**
	 * Display the details page for an individual product item.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	public function itemController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('ProductsModule::itemController');
		$this->applyViewDefaults($view);
		$this->applyConfigToView('page.item', $view);
		try {
			$view['item'] = $this->getItemRepository()->selectActiveItemByUrlPath($request->attributes->get('slug'));
		} catch (NoResultException $e) {
			throw new NotFoundHttpException('The requested product is not available.', $e);
		}
	}

	//-- AbstractUrlMountableModule Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function buildRoutes() {
		$routes = new RouteCollection();
		$routes->add('index', new Route($this->getMountedUrl()));
		$routes->add('category', new Route(sprintf('%s/%s/{slug}', $this->getMountedUrl(), $this->config('routes.category'))));
		$routes->add('item', new Route(sprintf('%s/%s/{slug}', $this->getMountedUrl(), $this->config('routes.item'))));
		return $routes;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildNavigationData($mode) {
		$data = $this->buildNavigationDataImpl($mode, intval($this->config('navigation.max-depth')));
		if ($mode === self::NAVIGATION_DATA_MODE_EXPANDED) {
			foreach ($this->getItemRepository()->getAllActiveItems() as $item) {
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
	 * Apply view defaults that are used by all pages in the products module.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 */
	private function applyViewDefaults(ViewInterface $view) {
		$view['title'] = $this->config('title');
		$view['category-path'] = trim($this->config('category-path'), '/');
		$view['item-path'] = trim($this->config('item-path'), '/');
		$view['item-url'] = $this->config('routes.item');
		$view['category-url'] = $this->config('routes.category');
		$view['root-url'] = $this->getMountedUrl();
	}

	/**
	 * Recursive method for navigation generation.
	 *
	 * @param int $mode
	 * @param int $maxDepth
	 * @param null|int|\Sitegear\Ext\Module\Products\Model\Category $root
	 *
	 * @return array
	 */
	private function buildNavigationDataImpl($mode, $maxDepth, $root=null) {
		$result = array();
		foreach ((is_null($root) ? $this->getCategoryRepository()->getRootCategories() : $this->getCategoryRepository()->getChildCategories($root)) as $category) {
			$categoryResult = array(
				'url' => sprintf('%s/%s/%s', $this->getMountedUrl(), $this->config('routes.category'), $category->getUrlPath()),
				'label' => $category->getName(),
				'tooltip' => sprintf('Find out about our range of "%s"', $category->getName())
			);
			if ($mode === self::NAVIGATION_DATA_MODE_EXPANDED || $maxDepth !== 1) { // >1 means more levels before the limit is reached, <=0 means no limit
				$subcategories = $this->buildNavigationDataImpl($mode, max(0, $maxDepth - 1), $category);
				if (!empty($subcategories)) {
					$categoryResult['children'] = $subcategories;
				}
			}
			$result[] = $categoryResult;
		}
		return $result;
	}

	//-- Internal Methods --------------------

	/**
	 * @return \Sitegear\Ext\Module\Products\Repository\ItemRepository
	 */
	private function getItemRepository() {
		return $this->getEngine()->doctrine()->getEntityManager()->getRepository('Products:Item');
	}

	/**
	 * @return \Sitegear\Ext\Module\Products\Repository\CategoryRepository
	 */
	private function getCategoryRepository() {
		return $this->getEngine()->doctrine()->getEntityManager()->getRepository('Products:Category');
	}

}
