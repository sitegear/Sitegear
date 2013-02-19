<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Customer;

use Sitegear\Base\Module\AbstractUrlMountableModule;
use Sitegear\Base\Module\PurchaseItemProviderModuleInterface;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

/**
 * Provides customer management functionality.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class CustomerModule extends AbstractUrlMountableModule {

	//-- Constants --------------------

	/**
	 * Session key to use for the trolley contents.
	 */
	const SESSION_KEY_TROLLEY = 'customer.trolley';

	/**
	 * Form key to use for the generated "add to trolley" form.
	 */
	const FORM_KEY_TROLLEY = 'trolley';

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
		$routes->add('removeTrolleyItem', new Route(sprintf('%s/remove-trolley-item', $this->getMountedUrl())));
		$routes->add('modifyTrolleyItem', new Route(sprintf('%s/modify-trolley-item', $this->getMountedUrl())));
		$routes->add('trolley', new Route(sprintf('%s/trolley', $this->getMountedUrl())));
		return $routes;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildNavigationData($mode) {
		return array();
	}

	//-- Page Controller Methods --------------------

	/**
	 * Show the customer profile page.
	 */
	public function indexController() {
		LoggerRegistry::debug('CustomerModule::indexController');
		// TODO Customer profile page
	}

	/**
	 * Handle the "add to trolley" action for any purchasable item.  This is the target of the "add to trolley" form.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function addTrolleyItemController(Request $request) {
		LoggerRegistry::debug('CustomerModule::addTrolleyItemController');
		// Extract request details.
		$moduleName = $request->request->get('module');
		$type = $request->request->get('type');
		$id = $request->request->get('id');
		// Setup the generated form.
		$this->getEngine()->forms()->registerForm(self::FORM_KEY_TROLLEY, $this->buildTrolleyForm($moduleName, $type, $id));
		// Validate the data against the generated form, and add the trolley item if valid.
		if ($valid = $this->getEngine()->forms()->validatePage(self::FORM_KEY_TROLLEY, 0, $request->request->all())) {
			$attributeValues = array();
			foreach ($request->request->all() as $key => $value) {
				if (strstr($key, 'attr_') !== false) {
					$attributeValues[substr($key, 5)] = $value;
				}
			}
			$this->addTrolleyItem($moduleName, $type, $id, $attributeValues, intval($request->request->get('qty')));
		}
		// Go back to the page where the submission was made.
		return new RedirectResponse($request->getUriForPath(
			$valid ?
				sprintf('/%s/trolley', $this->getMountedUrl()) :
				sprintf('/%s', $request->request->get('form-url'))
		));
	}

	/**
	 * Handle the "remove" button action from the trolley details page.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function removeTrolleyItemController(Request $request) {
		LoggerRegistry::debug('CustomerModule::removeTrolleyItemController');
		// Remove the item from the stored trolley data.
		$this->removeTrolleyItem(intval($request->request->get('index')));
		// Go back to the page where the submission was made.
		return new RedirectResponse($request->getUriForPath(sprintf('/%s/trolley', $this->getMountedUrl())));
	}

	/**
	 * Handle the quantity update from the trolley details page.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function modifyTrolleyItemController(Request $request) {
		LoggerRegistry::debug('CustomerModule::modifyTrolleyItemController');
		// Update the stored trolley data.
		$this->modifyTrolleyItem(intval($request->request->get('index')), intval($request->request->get('qty')));
		// Go back to the page where the submission was made.
		return new RedirectResponse($request->getUriForPath(sprintf('/%s/trolley', $this->getMountedUrl())));
	}

	/**
	 * Display the trolley details page.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 */
	public function trolleyController(ViewInterface $view) {
		LoggerRegistry::debug('CustomerModule::trolleyController');
		$this->applyConfigToView('pages.trolley', $view);
		$view['root-url'] = $this->getMountedUrl();
		$view['trolley-data'] = $this->getTrolleyData();
	}

	/**
	 * Display the checkout page.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 */
	public function checkoutController(ViewInterface $view) {
		LoggerRegistry::debug('CustomerModule::checkoutController');
		// TODO Checkout page
	}

	//-- Component Controller Methods --------------------

	/**
	 * Display the trolley preview, which is usually found in the site header.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 */
	public function trolleyPreviewComponent(ViewInterface $view) {
		LoggerRegistry::debug('CustomerModule::trolleyPreviewComponent');
		$this->applyConfigToView('components.trolley-preview', $view);
		$view['root-url'] = $this->getMountedUrl();
		$view['trolley-data'] = $this->getTrolleyData();
	}

	/**
	 * Display the "add to trolley" form.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param $moduleName
	 * @param $type
	 * @param $id
	 */
	public function trolleyFormComponent(ViewInterface $view, $moduleName, $type, $id) {
		LoggerRegistry::debug('CustomerModule::trolleyFormComponent');
		// Setup the generated form.
		$this->getEngine()->forms()->registerForm(self::FORM_KEY_TROLLEY, $this->buildTrolleyForm($moduleName, $type, $id));
		// Set the form key for the view to use.
		$view['form-key'] = self::FORM_KEY_TROLLEY;
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
		if ($qty < 1) {
			throw new \DomainException('CustomerModule cannot modify trolley item to a zero or negative quantity; use removeTrolleyItem instead.');
		}
		$module = $this->getPurchaseItemProviderModule($moduleName);
		$attributeDefinitions = $module->getPurchaseItemAttributeDefinitions($type, $id);
		$attributes = array();

		// Get an array of attributes, which each have a value and a label.
		foreach ($attributeValues as $attributeValue) {
			$attributeValue = intval($attributeValue);
			foreach ($attributeDefinitions as $attributeDefinition) {
				foreach ($attributeDefinition['options'] as $option) {
					if ($option['id'] === $attributeValue) {
						$attributes[] = array(
							'value' => $attributeValue,
							'label' => sprintf('%s: %s', $attributeDefinition['label'], $option['label'])
						);
					}
				}
			}
		}

		// Build the item data array.
		$item = array(
			'module' => $moduleName,
			'type' => $type,
			'id' => $id,
			'label' => $module->getPurchaseItemLabel($type, $id),
			'details-url' => $module->getPurchaseItemDetailsUrl($type, $id, $attributeValues),
			'attributes' => $attributes,
			'unit-price' => $module->getPurchaseItemUnitPrice($type, $id, $attributeValues),
			'qty' => $qty
		);

		// Add the item data to the trolley, or merge it in to an existing matching item.
		$data = $this->getTrolleyData();
		$matched = false;
		foreach ($data as $index => $record) {
			if (($record['module'] === $moduleName) && ($record['type'] === $type) && ($record['attributes'] === $attributes)) {
				$data[$index]['qty'] += $item['qty'];
				$matched = true;
			}
		}
		if (!$matched) {
			$data[] = $item;
		}
		$this->setTrolleyData($data);
	}

	/**
	 * Remove the trolley item at the given index.
	 *
	 * @param $index
	 *
	 * @throws \OutOfBoundsException
	 */
	public function removeTrolleyItem($index) {
		$data = $this->getTrolleyData();
		if ($index < 0 || $index >= sizeof($data)) {
			throw new \OutOfBoundsException(sprintf('CustomerModule cannot modify trolley item with index (%d) out-of-bounds', $index));
		}
		array_splice($data, $index, 1);
		$this->setTrolleyData($data);
	}

	/**
	 * Set the quantity of the trolley item at the given index.  The quantity must be greater than zero.
	 *
	 * @param $index
	 * @param $qty
	 *
	 * @throws \DomainException
	 * @throws \OutOfBoundsException
	 */
	public function modifyTrolleyItem($index, $qty) {
		if ($qty < 1) {
			throw new \DomainException('CustomerModule cannot modify trolley item to a zero or negative quantity; use removeTrolleyItem instead.');
		}
		$data = $this->getTrolleyData();
		if ($index < 0 || $index >= sizeof($data)) {
			throw new \OutOfBoundsException(sprintf('CustomerModule cannot modify trolley item with index (%d) out-of-bounds', $index));
		}
		$data[$index]['qty'] = $qty;
		$this->setTrolleyData($data);
	}

	//-- Internal Methods --------------------

	/**
	 * Retrieve a named module from the engine and check that it is an instance of PurchaseItemProviderModuleInterface.
	 * Essentially this is a shortcut to getEngine()->getModule() with an additional type check.
	 *
	 * @param $name
	 *
	 * @return \Sitegear\Base\Module\PurchaseItemProviderModuleInterface
	 * @throws \InvalidArgumentException
	 */
	protected function getPurchaseItemProviderModule($name) {
		$module = $this->getEngine()->getModule($name);
		if (!$module instanceof PurchaseItemProviderModuleInterface) {
			throw new \InvalidArgumentException(sprintf('The specified module "%s" is not a valid purchase item provider.', $name));
		}
		return $module;
	}

	/**
	 * Get the current contents of the trolley.
	 *
	 * @return array
	 */
	protected function getTrolleyData() {
		return $this->getEngine()->getSession()->get(self::SESSION_KEY_TROLLEY, array());
	}

	/**
	 * Set the contents of the trolley.
	 *
	 * @param array $data
	 */
	protected function setTrolleyData(array $data) {
		$this->getEngine()->getSession()->set(self::SESSION_KEY_TROLLEY, $data);
	}

	/**
	 * Dynamically generate the trolley form configuration.
	 *
	 * @param $moduleName
	 * @param $type
	 * @param $id
	 *
	 * @return array
	 */
	protected function buildTrolleyForm($moduleName, $type, $id) {
		// The first three fields are fixed, and they are all hidden fields.  This carries the information needed to determine
		// the unit price and other details of the project, once it is submitted to the Customer Module.
		$fieldDefinitions = array(
			'module' => array(
				'component' => 'input',
				'attributes' => array(
					'type' => 'hidden'
				),
				'default' => $moduleName
			),
			'type' => array(
				'component' => 'input',
				'attributes' => array(
					'type' => 'hidden'
				),
				'default' => $type
			),
			'id' => array(
				'component' => 'input',
				'attributes' => array(
					'type' => 'hidden'
				),
				'default' => $id
			)
		);
		$fields = array(
			'module',
			'type',
			'id'
		);

		// Every item attribute is an additional field in the form.
		foreach ($this->getPurchaseItemProviderModule($moduleName)->getPurchaseItemAttributeDefinitions($type, $id) as $attribute) { /** @var \Sitegear\Ext\Module\Products\Model\Attribute $attribute */
			$component = 'select'; // TODO Other field types
			$options = array();
			$noValueOptionLabel = $this->config('trolley-form.no-value-option-label');
			if (!is_null($noValueOptionLabel)) {
				$options[] = array(
					'value' => '',
					'label' => $noValueOptionLabel
				);
			}
			$labelFormat = $this->config('trolley-form.value-format');
			foreach ($attribute['options'] as $option) {
				$label = $labelFormat;
				$label = str_replace('%label%', $option['label'], $label);
				$label = str_replace('%value%', sprintf('$%s', number_format($option['value'] / 100, 2)), $label);
				$options[] = array(
					'value' => $option['id'],
					'label' => $label
				);
			}
			$name = sprintf('attr_%s', $attribute['id']);
			$fieldDefinitions[$name] = array(
				'component' => $component,
				'label' => $attribute['label'],
				'options' => $options,
				'validators' => array(
					array(
						'constraint' => 'not-blank'
					)
				)
			);
			$fields[] = $name;
		}

		// Add the quantity field, which is a standard text field with a label.
		$fieldDefinitions['qty'] = array(
			'component' => 'input',
			'label' => $this->config('trolley-form.quantity-label'),
			'default' => 1,
			'validators' => array(
				array(
					'constraint' => 'not-blank'
				),
				array(
					'constraint' => 'range',
					'options' => array(
						'min' => 1
					)
				)
			)
		);
		$fields[] = 'qty';

		// Display the combined form.
		return array(
			'action-url' => sprintf('%s/add-trolley-item', $this->getMountedUrl()),
			'submit-button' => $this->config('trolley-form.submit-button'),
			'reset-button' => false,
			'fields' => $fieldDefinitions,
			'pages' => array(
				array(
					'fieldsets' => array(
						array(
							'fields' => $fields
						)
					)
				)
			)
		);
	}

}
