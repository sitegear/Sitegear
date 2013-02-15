<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Customer;

use Sitegear\Base\Module\AbstractUrlMountableModule;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sitegear\Base\Module\PurchaseItemProviderModuleInterface;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

/**
 * Provides customer management functionality.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class CustomerModule extends AbstractUrlMountableModule {

	//-- Constants --------------------

	const SESSION_KEY_TROLLEY = 'customer.trolley';

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Customer Experience';
	}

	//-- AbstractUrlMountableModule Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function buildRoutes() {
		$routes = new RouteCollection();
		$routes->add('index', new Route($this->getMountedUrl()));
		$routes->add('addTrolleyItem', new Route(sprintf('%s/add-trolley-item', $this->getMountedUrl())));
		return $routes;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildNavigationData($mode) {
		return array();
	}

	//-- Page Controller Methods --------------------

	public function indexController() {
		// TODO Customer profile page
	}

	public function addTrolleyItemController(Request $request) {
		$attributeValues = array();
		foreach ($request->request->all() as $key => $value) {
			if (strstr($key, 'attr_') !== false) {
				$attributeValues[substr($key, 5)] = $value;
			}
		}
		$this->addTrolleyItem($request->request->get('module'), $request->request->get('type'), $request->request->get('id'), $attributeValues, $request->request->get('qty'));
		$targetUrl = $request->request->get('form-url');
		return new RedirectResponse($request->getUriForPath('/' . $targetUrl));
	}

	//-- Component Controller Methods --------------------

	public function trolleyFormComponent(ViewInterface $view, $moduleName, $type, $id) {
		$module = $this->getPurchaseItemProviderModule($moduleName);
		$view['root-url'] = $this->getMountedUrl();
		$view['module'] = $moduleName;
		$view['type'] = $type;
		$view['id'] = $id;
		$view['attribute-definitions'] = $module->getPurchaseItemAttributeDefinitions($type, $id);
	}

	//-- Public Methods --------------------

	/**
	 * Add a single item (of any quantity) to the trolley.
	 *
	 * @param string $moduleName Name of the module that provides the item being added.
	 * @param string $type Name of the item type being added.
	 * @param int $id Unique identifier of the item being added.
	 * @param array $attributeValues Attribute selections, a key-value array where the keys are attribute identifiers and
	 *   the values are value identifiers.
	 * @param int $qty Quantity being added, 1 by default.
	 *
	 * @throws \InvalidArgumentException
	 * @throws \DomainException
	 */
	public function addTrolleyItem($moduleName, $type, $id, array $attributeValues=null, $qty=null) {
		if (!is_null($qty) && $qty < 1) {
			throw new \DomainException(sprintf('Can\'t add %d of an item, must be a positive integer.', $qty));
		}
		$module = $this->getPurchaseItemProviderModule($moduleName);
		$item = array(
			'module' => $moduleName,
			'type' => $type,
			'id' => $id,
			'label' => $module->getPurchaseItemLabel($type, $id),
			'attribute-definitions' => $module->getPurchaseItemAttributeDefinitions($type, $id),
			'attribute-values' => $attributeValues,
			'unit-price' => $module->getPurchaseItemUnitPrice($type, $id, $attributeValues),
			'qty' => $qty ?: 1
		);
		$data = $this->getTrolleyData();
		$matched = false;
		foreach ($data as $index => $record) {
			if (($record['module'] === $moduleName) && ($record['type'] === $type) && ($record['attribute-values'] === $attributeValues)) {
				$data[$index]['qty'] += $item['qty'];
				$matched = true;
			}
		}
		if (!$matched) {
			$data[] = $item;
		}
		$this->setTrolleyData($data);
	}

	//-- Internal Methods --------------------

	protected function getPurchaseItemProviderModule($name) {
		$module = $this->getEngine()->getModule($name);
		if (!$module instanceof PurchaseItemProviderModuleInterface) {
			throw new \InvalidArgumentException(sprintf('The specified module "%s" is not a valid purchase item provider.', $name));
		}
		return $module;
	}

	protected function getTrolleyData() {
		return $this->getEngine()->getSession()->get(self::SESSION_KEY_TROLLEY, array());
	}

	protected function setTrolleyData(array $data) {
		$this->getEngine()->getSession()->set(self::SESSION_KEY_TROLLEY, $data);
	}

}
