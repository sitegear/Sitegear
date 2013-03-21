<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Customer;

use Sitegear\Base\Form\FormInterface;
use Sitegear\Base\Module\AbstractUrlMountableModule;
use Sitegear\Base\Module\PurchaseAdjustmentProviderModuleInterface;
use Sitegear\Base\Module\PurchaseItemProviderModuleInterface;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Ext\Module\Customer\Form\Builder\AddTrolleyItemFormBuilder;
use Sitegear\Ext\Module\Customer\Form\Builder\CheckoutFormBuilder;
use Sitegear\Ext\Module\Customer\Model\TransactionItem;
use Sitegear\Ext\Module\Customer\Model\Account;
use Sitegear\Util\UrlUtilities;
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
		// Register the Doctrine entity namespace.
		$this->getEngine()->doctrine()->getEntityManager()->getConfiguration()->addEntityNamespace(self::ENTITY_ALIAS, '\\Sitegear\\Ext\\Module\\Customer\\Model');
		// Register the checkout form generator.
		$this->getEngine()->forms()->registerFormGeneratorCallback($this->config('checkout.form-key'), array( $this, 'buildCheckoutForm' ));
	}

	//-- AbstractUrlMountableModule Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function buildRoutes() {
		$routes = new RouteCollection();
		$routes->add('index', new Route($this->getMountedUrl()));
		$routes->add('add-trolley-item', new Route(sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.add-trolley-item'))));
		$routes->add('remove-trolley-item', new Route(sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.remove-trolley-item'))));
		$routes->add('modify-trolley-item', new Route(sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.modify-trolley-item'))));
		$routes->add('trolley', new Route(sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.trolley'))));
		$routes->add('checkout', new Route(sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.checkout'))));
		$routes->add('thank-you', new Route(sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.thank-you'))));
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
		$view['account'] = $this->getLoggedInUserAccount();
		$view['fields'] = $this->getRepository('Field')->findAll();
		return null;
	}

	/**
	 * Handle the "add trolley item" action for any purchasable item.  This is the target of the "add trolley item" form.
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
		// Get the form URL.
		$formUrl = $request->query->get('form-url');
		// Setup the generated form.
		$form = $this->buildAddTrolleyItemForm($moduleName, $type, $id, $formUrl);
		$this->getEngine()->forms()->registerForm($this->config('add-trolley-item.form-key'), $form);
		// Validate the data against the generated form, and add the trolley item if valid.
		$errors = $this->getEngine()->forms()->validateForm($this->config('add-trolley-item.form-key'), $form->getStep(0)->getReferencedFields(), $request->request->all());
		if (empty($errors)) {
			$attributeValues = array();
			foreach ($request->request->all() as $key => $value) {
				if (strstr($key, 'attr_') !== false) {
					$attributeValues[substr($key, 5)] = $value;
				}
			}
			$this->addTrolleyItem($moduleName, $type, $id, $attributeValues, intval($request->request->get('quantity')));
			$this->getEngine()->forms()->resetForm($this->config('add-trolley-item.form-key'));
		}
		// Go back to the page where the submission was made.
		return new RedirectResponse($request->getUriForPath(
			empty($errors) ?
				sprintf('/%s/%s', $this->getMountedUrl(), $this->config('routes.trolley')) :
				sprintf('/%s', $formUrl)
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
		return new RedirectResponse($request->getUriForPath(sprintf('/%s/%s', $this->getMountedUrl(), $this->config('routes.trolley'))));
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
		return new RedirectResponse($request->getUriForPath(sprintf('/%s/%s', $this->getMountedUrl(), $this->config('routes.trolley'))));
	}

	/**
	 * Display the trolley page, which is mostly a wrapper for the trolley details component.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 *
	 * @throws \RuntimeException
	 */
	public function trolleyController(ViewInterface $view) {
		LoggerRegistry::debug('CustomerModule::trolleyController');
		$this->applyConfigToView('pages.trolley', $view);
		$view['trolley-data'] = $this->getTrolleyData();
	}

	/**
	 * Display the checkout page.
	 *
	 * @param ViewInterface $view
	 * @param Request $request
	 *
	 * @return RedirectResponse|null
	 */
	public function checkoutController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('CustomerModule::checkoutController');
		$trolleyData = $this->getTrolleyData();
		if (!empty($trolleyData)) {
			if ($this->getEngine()->getUserManager()->isLoggedIn()) {
				$this->applyConfigToView('pages.checkout', $view);
				$view['form-key'] = $this->config('checkout.form-key');
				$view['activate-script'] = $this->config('checkout.activate-script');
				return null;
			} else {
				$loginUrl = $this->getEngine()->userIntegration()->getAuthenticationLinkUrl('login', $request->getUri());
				return new RedirectResponse($request->getUriForPath(sprintf('/%s', $loginUrl)));
			}
		} else {
			return new RedirectResponse($request->getUriForPath(sprintf('/%s/%s', $this->getMountedUrl(), $this->config('routes.trolley'))));
		}
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
		$view['trolley-data'] = $this->getTrolleyData();
		$view['details-url'] = sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.trolley'));
		$view['checkout-url'] = sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.checkout'));
	}

	/**
	 * Display the trolley details component, which lists all items in the trolley, adjustments, and totals.
	 *
	 * @param ViewInterface $view
	 */
	public function trolleyDetailsComponent(ViewInterface $view) {
		LoggerRegistry::debug('CustomerModule::trolleyDetailsComponent');
		$this->applyConfigToView('components.trolley-details', $view);
		$view['modify-item-url'] = sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.modify-trolley-item'));
		$view['remove-item-url'] = sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.remove-trolley-item'));
		$view['form-url'] = sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.trolley'));
		$view['checkout-url'] = sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.checkout'));
		$view['trolley-data'] = $this->getTrolleyData();
		$view['adjustments'] = $this->getAdjustments();
	}

	/**
	 * Display the "add trolley item" form.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param Request $request
	 * @param $moduleName
	 * @param $type
	 * @param $id
	 */
	public function addTrolleyItemFormComponent(ViewInterface $view, Request $request, $moduleName, $type, $id) {
		LoggerRegistry::debug('CustomerModule::addTrolleyItemFormComponent');
		$formKey = $view['form-key'] = $this->config('add-trolley-item.form-key');
		$this->getEngine()->forms()->registerForm($formKey, $this->buildAddTrolleyItemForm($moduleName, $type, $id, $request->getPathInfo()));
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
		LoggerRegistry::debug('CustomerModule::addTrolleyItem');
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
				foreach ($attributeDefinition['values'] as $value) {
					if ($value['id'] === $attributeValue) {
						$attributes[] = array(
							'value' => $attributeValue,
							'label' => sprintf('%s: %s', $attributeDefinition['label'], $value['label'])
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
		LoggerRegistry::debug('CustomerModule::removeTrolleyItem');
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
		LoggerRegistry::debug('CustomerModule::modifyTrolleyItem');
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

	/**
	 * Prepare the payment.
	 */
	public function preparePayment() {
		LoggerRegistry::debug('CustomerModule::preparePayment');
		// TODO Payment gateway integration
	}

	/**
	 * Make the payment.  This requires payment details.
	 */
	public function makePayment() {
		LoggerRegistry::debug('CustomerModule::makePayment');
		// TODO Payment gateway integration
	}

	/**
	 * Utilise AddTrolleyItemFormBuilder to create the 'add trolley item' form.
	 *
	 * @param string $moduleName
	 * @param string $type
	 * @param integer $id
	 * @param string $formUrl
	 *
	 * @return \Sitegear\Base\Form\FormInterface
	 */
	public function buildAddTrolleyItemForm($moduleName, $type, $id, $formUrl) {
		LoggerRegistry::debug('CustomerModule::buildAddTrolleyItemForm');
		$submitUrl = sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.add-trolley-item'));
		$submitUrl = UrlUtilities::generateLinkWithReturnUrl($submitUrl, ltrim($formUrl, '/'), 'form-url');
		$formBuilder = new AddTrolleyItemFormBuilder($this->getEngine()->forms(), $this->config('add-trolley-item.form-key'));
		$form = $formBuilder->buildForm(array(
			'module-name' => $moduleName,
			'type' => $type,
			'id' => $id,
			'submit-url' => $submitUrl,
			'labels' => array(
				'quantity-field' => $this->config('add-trolley-item.quantity-field'),
				'no-value-option' => $this->config('add-trolley-item.no-value-option'),
				'value-format' => $this->config('add-trolley-item.value-format')
			)
		));
		return $form;
	}

	/**
	 * Utilise CheckoutFormBuilder to create the checkout form.
	 *
	 * @return FormInterface
	 */
	public function buildCheckoutForm() {
		LoggerRegistry::debug('CustomerModule::buildCheckoutForm');
		$steps = $this->config('checkout.steps.current');
		if (is_string($steps)) {
			$steps = $this->config(sprintf('checkout.steps.built-in.%s', $steps));
		}
		$builder = new CheckoutFormBuilder($this->getEngine()->forms(), $this->config('checkout.form-key'), $this->getLoggedInUserAccount());
		$form = $builder->buildForm(array(
			'form-url' => sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.checkout')),
			'target-url' => sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.thank-you')),
			'cancel-url' => sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.trolley')),
			'fields' => $this->config('checkout.fields'),
			'fieldsets' => $this->config('checkout.fieldsets'),
			'steps' => $steps
		));
		return $form;
	}

	/**
	 * Get the Account entity for the logged in user.  Create and persist the entity if necessary.
	 *
	 * @return Account|null
	 */
	public function getLoggedInUserAccount() {
		LoggerRegistry::debug('CustomerModule::getLoggedInUserAccount');
		$email = $this->getEngine()->getUserManager()->getLoggedInUserEmail();
		$account = $this->getRepository('Account')->findOneBy(array( 'email' => 'ben@leftclick.com.au' ));
		if (is_null($account)) {
			$account = new Account();
			$account->setEmail($email);
			$this->getEngine()->doctrine()->getEntityManager()->persist($account);
		}
		return $account;
	}

	//-- Internal Methods --------------------

	/**
	 * Get the current contents of the trolley.
	 *
	 * @return TransactionItem[]
	 */
	protected function getTrolleyData() {
		return $this->getEngine()->getSession()->get(self::SESSION_KEY_TROLLEY, array());
	}

	/**
	 * Set the contents of the trolley.
	 *
	 * @param TransactionItem[] $data
	 */
	protected function setTrolleyData(array $data) {
		$this->getEngine()->getSession()->set(self::SESSION_KEY_TROLLEY, $data);
	}

	/**
	 * Get an array of key-value arrays specifying the label and value for each configured adjustment.
	 *
	 * @return array[]
	 *
	 * @throws \RuntimeException
	 */
	private function getAdjustments() {
		$adjustments = array();
		foreach ($this->config('checkout.adjustments') as $name) {
			$module = $this->getEngine()->getModule($name);
			if (is_null($module)) {
				throw new \RuntimeException(sprintf('FormsModule found invalid entry in "checkout.adjustments"; module "%s" does not exist', $name));
			} elseif (!$module instanceof PurchaseAdjustmentProviderModuleInterface) {
				throw new \RuntimeException(sprintf('FormsModule found invalid entry in "checkout.adjustments"; must be a purchase adjustment provider module, found "%s"', $name));
			}
			// TODO Pass in $data array to getAdjustmentAmount()
			$value = $module->getAdjustmentAmount($this->getTrolleyData(), array());
			if (!empty($value) || $module->isVisibleUnset()) {
				$adjustments[] = array(
					'label' => $module->getAdjustmentLabel(),
					'value' => $value
				);
			}
		}
		return $adjustments;
	}

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
	 * @param string $entity
	 *
	 * @return \Doctrine\ORM\EntityRepository
	 */
	private function getRepository($entity) {
		return $this->getEngine()->doctrine()->getEntityManager()->getRepository(sprintf('%s:%s', self::ENTITY_ALIAS, $entity));
	}

}
