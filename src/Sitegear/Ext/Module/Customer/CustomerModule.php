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
use Sitegear\Ext\Module\Customer\Model\TransactionItem;
use Sitegear\Ext\Module\Customer\Model\Account;
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
	 * Alias to use for this module's entity namespace.
	 */
	const ENTITY_ALIAS = 'Customer';

	/**
	 * Session key to use for the trolley contents.
	 */
	const SESSION_KEY_TROLLEY = 'customer.trolley';

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Customer Experience';
	}

	/**
	 * {@inheritDoc}
	 */
	public function start() {
		LoggerRegistry::debug('CustomerModule starting');
		$this->getEngine()->doctrine()->getEntityManager()->getConfiguration()->addEntityNamespace(self::ENTITY_ALIAS, '\\Sitegear\\Ext\\Module\\Customer\\Model');
	}

	//-- AbstractUrlMountableModule Methods --------------------

	/**
	 * {@inheritDoc}
	 *
	 * TODO Route requirements??
	 */
	protected function buildRoutes() {
		$routes = new RouteCollection();
		$routes->add('index', new Route($this->getMountedUrl()));
		$routes->add('addTrolleyItem', new Route(sprintf('%s/add-trolley-item', $this->getMountedUrl())));
		$routes->add('removeTrolleyItem', new Route(sprintf('%s/remove-trolley-item', $this->getMountedUrl())));
		$routes->add('modifyTrolleyItem', new Route(sprintf('%s/modify-trolley-item', $this->getMountedUrl())));
		$routes->add('trolley', new Route(sprintf('%s/trolley', $this->getMountedUrl())));
		$routes->add('checkout', new Route(sprintf('%s/checkout', $this->getMountedUrl())));
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
	public function indexController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('CustomerModule::indexController');
		$this->applyConfigToView('pages.index', $view);
		if (!$this->getEngine()->getUserManager()->isLoggedIn()) {
			return new RedirectResponse($this->getEngine()->userIntegration()->getAuthenticationLinkUrl('login', $request->getUri()));
		}
		$email = $this->getEngine()->getUserManager()->getLoggedInUserEmail();
		$account = $this->getRepository('Account')->findOneBy(array( 'email' => $email ));
		if (is_null($account)) {
			$account = new Account();
			$account->setEmail($email);
			$this->getEngine()->doctrine()->getEntityManager()->persist($account);
		}
		$view['account'] = $account;
		$view['fields'] = $this->getRepository('Field')->findAll();
		return null;
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
		$formKey = $this->config('trolley-form.form-key');
		$this->getEngine()->forms()->registerForm($formKey, $this->buildTrolleyForm($moduleName, $type, $id));
		// Validate the data against the generated form, and add the trolley item if valid.
		if ($valid = $this->getEngine()->forms()->validateForm($formKey, 0, $request->request->all())) {
			$attributeValues = array();
			foreach ($request->request->all() as $key => $value) {
				if (strstr($key, 'attr_') !== false) {
					$attributeValues[substr($key, 5)] = $value;
				}
			}
			$this->addTrolleyItem($moduleName, $type, $id, $attributeValues, intval($request->request->get('quantity')));
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
		$this->modifyTrolleyItem(intval($request->request->get('index')), intval($request->request->get('quantity')));
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
		$this->applyConfigToView('pages.checkout', $view);
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
		$formKey = $view['form-key'] = $this->config('trolley-form.form-key');
		$this->getEngine()->forms()->registerForm($formKey, $this->buildTrolleyForm($moduleName, $type, $id));
	}

	//-- Public Methods --------------------

	/**
	 * Add a single item (of any quantity) to the trolley.
	 *
	 * @param string $moduleName Name of the module that provides the item being added.
	 * @param string $type Name of the item type being added.
	 * @param int $itemId Unique identifier of the item being added.
	 * @param array $attributeValues Attribute selections, a key-value array where the keys are attribute identifiers and
	 *   the values are value identifiers.
	 * @param int $quantity Quantity being added, 1 by default.
	 *
	 * @throws \InvalidArgumentException
	 * @throws \DomainException
	 */
	public function addTrolleyItem($moduleName, $type, $itemId, array $attributeValues=null, $quantity=null) {
		if ($quantity < 1) {
			throw new \DomainException('CustomerModule cannot modify trolley item to a zero or negative quantity; use removeTrolleyItem instead.');
		}
		$module = $this->getPurchaseItemProviderModule($moduleName);
		$attributeDefinitions = $module->getPurchaseItemAttributeDefinitions($type, $itemId);
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

		// Add the item data to the trolley, or merge it in to an existing matching item.
		$data = $this->getTrolleyData();
		$matched = false;
		foreach ($data as $index => $item) { /** @var TransactionItem $item */
			if (($item->getModule() === $moduleName) && ($item->getType() === $type) && ($item->getAttributes() === $attributes)) {
				$matched = $index;
			}
		}
		if ($matched !== false) {
			$item = $data[$matched];
			$item->setQuantity($item->getQuantity() + $quantity);
			$data[$matched] = $item;
		} else {
			$item = new TransactionItem();
			$item->setModule($moduleName);
			$item->setType($type);
			$item->setItemId($itemId);
			$item->setLabel($module->getPurchaseItemLabel($type, $itemId));
			$item->setDetailsUrl($module->getPurchaseItemDetailsUrl($type, $itemId, $attributeValues));
			$item->setAttributes($attributes);
			$item->setUnitPrice($module->getPurchaseItemUnitPrice($type, $itemId, $attributeValues));
			$item->setQuantity($quantity);
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
	 * @param $quantity
	 *
	 * @throws \DomainException
	 * @throws \OutOfBoundsException
	 */
	public function modifyTrolleyItem($index, $quantity) {
		if ($quantity < 1) {
			throw new \DomainException('CustomerModule cannot modify trolley item to a zero or negative quantity; use removeTrolleyItem instead.');
		}
		$data = $this->getTrolleyData();
		if ($index < 0 || $index >= sizeof($data)) {
			throw new \OutOfBoundsException(sprintf('CustomerModule cannot modify trolley item with index (%d) out-of-bounds', $index));
		}
		$item = $data[$index]; /** @var TransactionItem $item */
		$item->setQuantity($quantity);
		$data[$index] = $item;
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
				$label = \Sitegear\Util\TokenUtilities::replaceTokens(
					$labelFormat,
					array(
						'label' => $option['label'],
						'value' => sprintf('$%s', number_format($option['value'] / 100, 2))
					)
				);
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
		$fieldDefinitions['quantity'] = array(
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
		$fields[] = 'quantity';

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

	/**
	 * @param string $entity
	 *
	 * @return \Doctrine\ORM\EntityRepository
	 */
	private function getRepository($entity) {
		return $this->getEngine()->doctrine()->getEntityManager()->getRepository(sprintf('%s:%s', self::ENTITY_ALIAS, $entity));
	}

}
