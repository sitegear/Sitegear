<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Products;

use Sitegear\Base\Module\PurchaseItemProviderModuleInterface;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Module\AbstractCoreModule;
use Sitegear\Module\Products\Model\Attribute;
use Sitegear\Module\Products\Model\Item;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Doctrine\ORM\NoResultException;

/**
 * Displays and allows management of products in a product category hierarchy.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class ProductsModule extends AbstractCoreModule implements PurchaseItemProviderModuleInterface {

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getDisplayName() {
		return 'Products Catalogue';
	}

	//-- PurchaseItemProviderModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getPurchaseItemLabel($type, $id) {
		$result = null;
		switch ($type) {
			case 'item':
				$result = $this->getRepository('Item')->find($id)->getName();
				break;
			default:
				throw new \InvalidArgumentException(sprintf('ProductsModule encountered unknown type "%s" when retrieving label for purchase item', $type));
		}
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function getPurchaseItemAttributeDefinitions($type, $id) {
		$result = array();
		switch ($type) {
			case 'item':
				foreach ($this->getRepository('Item')->find($id)->getAttributeAssignments() as $assignment) { /** @var \Sitegear\Module\Products\Model\AttributeAssignment $assignment */
					$values = array();
					foreach ($assignment->getAttribute()->getOptions() as $option) { /** @var \Sitegear\Module\Products\Model\AttributeOption $option */
						$values[] = array(
							'id' => $option->getId(),
							'value' => $option->getValue(),
							'label' => $option->getLabel()
						);
					}
					$result[] = array(
						'id' => $assignment->getAttribute()->getId(),
						'label' => $assignment->getAttribute()->getLabel(),
						'values' => $values
					);
				}
				break;
			default:
				throw new \InvalidArgumentException(sprintf('ProductsModule encountered unknown type "%s" when retrieving label for purchase item', $type));
		}
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function getPurchaseItemUnitPrice($type, $id, array $attributeValues) {
		$basePrice = null;
		$adjustments = 0;
		foreach ($attributeValues as $attributeValue) {
			/** @var \Sitegear\Module\Products\Model\AttributeOption $attributeOption */
			$attributeOption = $this->getRepository('AttributeOption')->find($attributeValue);
			switch ($attributeOption->getAttribute()->getType()) {
				case Attribute::TYPE_BASE:
					$basePrice = $attributeOption->getValue();
					break;
				case Attribute::TYPE_SINGLE:
				case Attribute::TYPE_MULTIPLE:
					$adjustments = $attributeOption->getValue();
					break;
			}
		}
		return $basePrice + $adjustments;
	}

	/**
	 * @inheritdoc
	 */
	public function getPurchaseItemDetailsUrl($type, $id, array $attributeValues) {
		return $this->getRouteUrl('item', $this->getRepository('Item')->find($id)->getUrlPath());
	}

	//-- AbstractUrlMountableModule Methods --------------------

	/**
	 * @inheritdoc
	 */
	protected function buildNavigationData($mode) {
		$data = $this->buildNavigationDataImpl($mode, intval($this->config('navigation.max-depth')));
		if ($mode === self::NAVIGATION_DATA_MODE_EXPANDED) {
			foreach ($this->getRepository('Item')->findByActive(true) as $item) {
				/** @var Item $item */
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
	 * Display the main shop entrance page.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 */
	public function indexController(ViewInterface $view) {
		LoggerRegistry::debug('ProductsModule::indexController');
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
		try {
			$view['item'] = $this->getRepository('Item')->findOneBy(array( 'urlPath' => $request->attributes->get('slug'), 'active' => true ));
		} catch (NoResultException $e) {
			throw new NotFoundHttpException('The requested product is not available.', $e);
		}
	}

	//-- Internal Methods --------------------

	/**
	 * Recursive method for navigation generation.
	 *
	 * @param int $mode
	 * @param int $maxDepth
	 * @param null|int|\Sitegear\Module\Products\Model\Category $parent
	 *
	 * @return array
	 */
	private function buildNavigationDataImpl($mode, $maxDepth, $parent=null) {
		$result = array();
		foreach ($this->getRepository('Category')->findByParent($parent) as $category) {
			/** @var \Sitegear\Module\Products\Model\Category $category */
			$tooltip = \Sitegear\Util\TokenUtilities::replaceTokens(
				$this->config('navigation.tooltip'),
				array(
					'categoryName' => $category->getName()
				)
			);
			$categoryResult = array(
				'url' => $this->getRouteUrl('category', $category->getUrlPath()),
				'label' => $category->getName(),
				'tooltip' => $tooltip
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

}
