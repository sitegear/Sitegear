<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Products;

use Sitegear\Base\Module\AbstractUrlMountableModule;
use Sitegear\Base\Module\PurchaseItemProviderModuleInterface;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Util\LoggerRegistry;

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
class ProductsModule extends AbstractUrlMountableModule implements PurchaseItemProviderModuleInterface {

	//-- Constants --------------------

	const ENTITY_ALIAS = 'Products';

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
		$this->getEngine()->doctrine()->getEntityManager()->getConfiguration()->addEntityNamespace(self::ENTITY_ALIAS, '\\Sitegear\\Ext\\Module\\Products\\Model');
	}

	//-- PurchaseItemProviderModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getPurchaseItemLabel($type, $id) {
		$result = null;
		switch ($type) {
			case 'item':
				$result = $this->getRepository('item')->find($id)->getName();
				break;
			default:
				throw new \InvalidArgumentException(sprintf('ProductsModule encountered unknown type "%s" when retrieving label for purchase item', $type));
		}
		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPurchaseItemAttributeDefinitions($type, $id) {
		$result = array();
		switch ($type) {
			case 'item':
				foreach ($this->getRepository('item')->find($id)->getAttributeAssignments() as $assignment) { /** @var \Sitegear\Ext\Module\Products\Model\AttributeAssignment $assignment */
					$options = array();
					foreach ($assignment->getAttribute()->getOptions() as $option) { /** @var \Sitegear\Ext\Module\Products\Model\AttributeOption $option */
						$options[$option->getLabel()] = $option->getValue();
					}
					$result[$assignment->getAttribute()->getLabel()] = $options;
				}
				break;
			default:
				throw new \InvalidArgumentException(sprintf('ProductsModule encountered unknown type "%s" when retrieving label for purchase item', $type));
		}
		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPurchaseItemUnitPrice($type, $id, array $attributeValues) {
		// TODO: Implement getPurchaseItemUnitPrice() method.
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
			foreach ($this->getRepository('Item')->findByActive(true) as $item) {
				$data[] = array(
					'url' => sprintf('%s/%s/%s', $this->getMountedUrl(), $this->config('routes.item'), $item->getUrlPath()),
					'label' => $item->getName()
				);
			}
		}
		return $data;
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
		$view['categories'] = $this->getRepository('Category')->findByParent(null);
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
		$view['category'] = $this->getRepository('Category')->findOneByUrlPath($request->attributes->get('slug'));
		$view['categories'] = $this->getRepository('Category')->findByParent($view['category']);
		$view['items'] = $this->getRepository('Item')->getActiveItemsInCategory($view['category']);
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
			$view['item'] = $this->getRepository('Item')->findOneBy(array( 'urlPath' => $request->attributes->get('slug'), 'active' => true ));
		} catch (NoResultException $e) {
			throw new NotFoundHttpException('The requested product is not available.', $e);
		}
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
	 * @param null|int|\Sitegear\Ext\Module\Products\Model\Category $parent
	 *
	 * @return array
	 */
	private function buildNavigationDataImpl($mode, $maxDepth, $parent=null) {
		$result = array();
		foreach ($this->getRepository('Category')->findByParent($parent) as $category) {
			/** @var \Sitegear\Ext\Module\Products\Model\Category $category */
			$categoryResult = array(
				'url' => sprintf('%s/%s/%s', $this->getMountedUrl(), $this->config('routes.category'), $category->getUrlPath()),
				'label' => $category->getName(),
				// TODO Make this configurable
				'tooltip' => sprintf('Find out about our range of "%s"', $category->getName())
			);
			if ($mode === self::NAVIGATION_DATA_MODE_EXPANDED || $maxDepth !== 1) { // >1 means more levels before the limit is reached, <=0 means no limit
				$subCategories = $this->buildNavigationDataImpl($mode, max(0, $maxDepth - 1), $category);
				if (!empty($subCategories)) {
					$categoryResult['children'] = $subCategories;
				}
			}
			$result[] = $categoryResult;
		}
		return $result;
	}

	//-- Internal Methods --------------------

	/**
	 * @param string $entity
	 *
	 * @return \Doctrine\ORM\EntityRepository
	 */
	private function getRepository($entity) {
		return $this->getEngine()->doctrine()->getEntityManager()->getRepository(sprintf('%s:%s', self::ENTITY_ALIAS, $entity));
	}

}
