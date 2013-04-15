<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Customer;

use Sitegear\Base\Form\FormInterface;
use Sitegear\Base\Module\PurchaseAdjustmentProviderModuleInterface;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Module\AbstractCoreModule;
use Sitegear\Module\Customer\Form\Builder\AddTrolleyItemFormBuilder;
use Sitegear\Module\Customer\Form\Builder\CheckoutFormBuilder;
use Sitegear\Module\Customer\Model\Account;
use Sitegear\Util\UrlUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides customer management functionality.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class CustomerModule extends AbstractCoreModule {

	//-- Attributes --------------------

	/**
	 * @var Trolley
	 */
	private $trolley;

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getDisplayName() {
		return 'Customer Experience';
	}

	/**
	 * @inheritdoc
	 */
	public function start() {
		parent::start();
		// Register the checkout form generator.
		$this->getEngine()->forms()->registry()->registerFormGeneratorCallback($this->config('checkout.form-key'), array( $this, 'buildCheckoutForm' ));
		// Create the trolley object.
		$this->trolley = new Trolley($this, $this->config('page-messages'));
	}

	//-- Page Controller Methods --------------------

	/**
	 * Show the customer profile page.
	 */
	public function indexController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('CustomerModule::indexController');
		if (!$this->getEngine()->getUserManager()->isLoggedIn()) {
			return new RedirectResponse($this->getEngine()->userIntegration()->getAuthenticationLinkUrl('login', $request));
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
		$formUrl = UrlUtilities::absoluteUrl($request->query->get('form-url'), $request);
		// Setup the generated form.
		$form = $this->buildAddTrolleyItemForm($moduleName, $type, $id, $formUrl);
		$formKey = $this->config('add-trolley-item.form-key');
		$this->getEngine()->forms()->registry()->registerForm($formKey, $form);
		// Validate the data against the generated form, and add the trolley item if valid.
		$errors = $this->getEngine()->forms()->registry()->validateForm($formKey, $form->getStep(0)->getReferencedFields(), $request->request->all());
		$this->getEngine()->forms()->registry()->setValues($formKey, $request->request->all());
		if (empty($errors)) {
			$attributeValues = array();
			foreach ($request->request->all() as $key => $value) {
				if (strstr($key, 'attr_') !== false) {
					$attributeValues[substr($key, 5)] = $value;
				}
			}
			$this->trolley()->addItem($moduleName, $type, $id, $attributeValues, intval($request->request->get('quantity')));
			$this->getEngine()->forms()->registry()->resetForm($formKey);
		}
		// Go back to the page where the submission was made.
		return new RedirectResponse(UrlUtilities::absoluteUrl($formUrl, $request));
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
		$this->trolley()->removeItem(intval($request->request->get('index')));
		// Go back to the page where the submission was made.
		return new RedirectResponse(UrlUtilities::absoluteUrl($this->getRouteUrl('trolley'), $request));
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
		$this->trolley()->modifyItem(intval($request->request->get('index')), intval($request->request->get('quantity')));
		// Go back to the page where the submission was made.
		return new RedirectResponse(UrlUtilities::absoluteUrl($this->getRouteUrl('trolley'), $request));
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
		$view['trolley-data'] = $this->trolley()->getData();
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
		$trolleyData = $this->trolley()->getData();
		if (!empty($trolleyData)) {
			if ($this->getEngine()->getUserManager()->isLoggedIn()) {
				$view['form-key'] = $this->config('checkout.form-key');
				$view['activate-script'] = $this->config('checkout.activate-script');
				return null;
			} else {
				return new RedirectResponse($this->getEngine()->userIntegration()->getAuthenticationLinkUrl('login', $request));
			}
		} else {
			return new RedirectResponse($this->getRouteUrl('trolley'));
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
		$view['trolley-data'] = $this->trolley()->getData();
		$view['details-url'] = $this->getRouteUrl('trolley');
		$view['checkout-url'] = UrlUtilities::generateLinkWithReturnUrl(
			$this->getEngine()->forms()->getRouteUrl('initialise', $this->config('checkout.form-key')),
			$this->getRouteUrl('checkout'),
			'form-url'
		);
	}

	/**
	 * Display the trolley details component, which lists all items in the trolley, adjustments, and totals.
	 *
	 * @param ViewInterface $view
	 */
	public function trolleyDetailsComponent(ViewInterface $view) {
		LoggerRegistry::debug('CustomerModule::trolleyDetailsComponent');
		$view['modify-item-url'] = $this->getRouteUrl('modify-trolley-item');
		$view['remove-item-url'] = $this->getRouteUrl('remove-trolley-item');
		$view['form-url'] = $this->getRouteUrl('trolley');
		$view['trolley-data'] = $this->trolley()->getData();
		$view['adjustments'] = $this->getAdjustments();
		$view['checkout-url'] = UrlUtilities::generateLinkWithReturnUrl(
			$this->getEngine()->forms()->getRouteUrl('initialise', $this->config('checkout.form-key')),
			$this->getRouteUrl('checkout'),
			'form-url'
		);
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
		$this->getEngine()->forms()->registry()->registerForm($formKey, $this->buildAddTrolleyItemForm($moduleName, $type, $id, $request->getUri()));
	}

	//-- Public Methods --------------------

	/**
	 * @return Trolley
	 */
	public function trolley() {
		return $this->trolley;
	}

	/**
	 * Prepare the payment.
	 */
	public function preparePayment() {
		LoggerRegistry::debug('Trolley::preparePayment');
		// TODO Payment gateway integration
	}

	/**
	 * Make the payment.  This requires payment details.
	 */
	public function makePayment() {
		LoggerRegistry::debug('Trolley::makePayment');
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
		$submitUrl = $this->getRouteUrl('add-trolley-item');
		$submitUrl = UrlUtilities::generateLinkWithReturnUrl($submitUrl, $formUrl, 'form-url');
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
			'form-url' => $this->getRouteUrl('checkout'),
			'target-url' => $this->getRouteUrl('thank-you'),
			'cancel-url' => $this->getRouteUrl('trolley'),
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
		$account = null;
		$email = $this->getEngine()->getUserManager()->getLoggedInUserEmail();
		if (!is_null($email)) {
			$account = $this->getRepository('Account')->findOneBy(array( 'email' => 'ben@leftclick.com.au' ));
			if (is_null($account)) {
				$account = new Account();
				$account->setEmail($email);
				$this->getEngine()->doctrine()->getEntityManager()->persist($account);
			}
		}
		return $account;
	}

	//-- Internal Methods --------------------

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
			$value = $module->getAdjustmentAmount($this->trolley()->getData(), array());
			if (!empty($value) || $module->isVisibleUnset()) {
				$adjustments[] = array(
					'label' => $module->getAdjustmentLabel(),
					'value' => $value
				);
			}
		}
		return $adjustments;
	}

}
