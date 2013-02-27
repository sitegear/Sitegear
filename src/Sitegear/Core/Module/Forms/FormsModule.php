<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\Forms;

use Sitegear\Base\Module\AbstractUrlMountableModule;
use Sitegear\Base\Config\Processor\ArrayTokenProcessor;
use Sitegear\Base\Config\Processor\ConfigTokenProcessor;
use Sitegear\Base\Config\Container\SimpleConfigContainer;
use Sitegear\Base\Engine\EngineInterface;
use Sitegear\Base\Resources\ResourceLocations;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Base\Form\FormInterface;
use Sitegear\Base\Form\Field\FieldInterface;
use Sitegear\Base\Form\Processor\FormProcessorInterface;
use Sitegear\Core\Form\Builder\FormBuilder;
use Sitegear\Util\NameUtilities;
use Sitegear\Util\TypeUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * Displays and allows management of programmable HTML forms.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class FormsModule extends AbstractUrlMountableModule {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Core\Form\Builder\FormBuilder
	 */
	private $builder;

	/**
	 * @var FormInterface[]
	 */
	private $forms;

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Web Forms';
	}

	/**
	 * {@inheritDoc}
	 */
	public function start() {
		$this->builder = new FormBuilder($this->getEngine());
		$this->forms = array();
	}

	//-- AbstractUrlMountableModule Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function buildRoutes() {
		$routes = new RouteCollection();
		$routes->add('form', new Route(sprintf('%s/form/{slug}', $this->getMountedUrl())));
		$routes->add('reset', new Route(sprintf('%s/reset/{slug}', $this->getMountedUrl())));
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
	 * Handles a form submission.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 *
	 * @throws \RuntimeException
	 */
	public function formController(Request $request) {
		LoggerRegistry::debug('FormsModule::formController()');

		// Get the form and submission details
		$formKey = $request->attributes->get('slug');
		$form = $this->getForm($formKey, $request);
		$formUrl = $request->getUriForPath('/' . $request->query->get('form-url', ''));
		$targetUrl = null;
		$currentStep = $request->request->get('step', 0);
		$currentStepSessionKey = $this->getSessionKey($formKey, 'step');
		$back = $request->request->get('back');
		$step = $form->getStep($currentStep);
		$fields = $step->getRootElement()->getAncestorFields();
		$data = $request->request->all();
		unset($data['step']);
		unset($data['back']);

		// Set the values into the session so they can be displayed after redirecting, and clear previous errors.
		$session = $this->getEngine()->getSession();
		$valuesSessionKey = $this->getSessionKey($formKey, 'values');
		$session->set($valuesSessionKey, array_merge($session->get($valuesSessionKey, array()), $data));

		if ($back) {
			// The "back" button was clicked
			$currentStep = max(0, $currentStep - 1);
			$this->getEngine()->getSession()->set($currentStepSessionKey, $currentStep);
		} else {
			// Perform validation
			if ($valid = $this->validate($formKey, $fields, $data)) {
				// No errors, so execute processors
				foreach ($step->getProcessors() as $processor) {
					$arguments = $this->parseProcessorArguments($processor, $data);
					TypeUtilities::invokeCallable($processor->getCallable(), null, array( $request ), $arguments);
				}
				// Redirect to the target URL, which is either the actual target-url, if the form is finished and there
				// is one set, or to the form URL, or to the home page as a fallback
				if (++$currentStep >= $form->getStepsCount()) {
					$this->resetForm($formKey);
					$targetUrl = $request->getUriForPath('/' . (!is_null($form->getTargetUrl()) ? $form->getTargetUrl() : ''));
				} else {
					$this->getEngine()->getSession()->set($currentStepSessionKey, $currentStep);
				}
			}
		}
		return new RedirectResponse($targetUrl ?: $formUrl);
	}

	/**
	 * Reset the given form and optionally set request query (GET) parameters into the form values, then redirect to
	 * the form URL, which must be supplied as a query (GET) parameter or the home page will be used as a default.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function resetController(Request $request) {
		LoggerRegistry::debug('FormsModule::resetController()');
		$formKey = $request->attributes->get('slug');
		$this->resetForm($formKey);
		$form = $this->getForm($formKey, $request);
		$formUrl = $request->getUriForPath('/' . $request->query->get('form-url', ''));
		$data = array();
		foreach ($request->query->all() as $key => $value) {
			if (($key !== 'form-url') && !is_null($form->getField($key))) {
				$field = $form->getField($key);
				if (!is_null($field)) {
					$field->setValue($value);
					$data[$key] = $value;
				}
			}
		}
		$this->getEngine()->getSession()->set($this->getSessionKey($formKey, 'values'), $data);
		return new RedirectResponse($formUrl);
	}

	//-- Component Controller Methods --------------------

	/**
	 * Display a form.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param string $formKey Unique key of the form, used for session storage and also is the key used to retrieve the
	 *   form data, if it is not supplied directly.
	 */
	public function formComponent(ViewInterface $view, Request $request, $formKey) {
		LoggerRegistry::debug('FormsModule::formComponent()');
		$view['form'] = $this->getForm($formKey, $request);
		$view['current-step'] = $this->getEngine()->getSession()->get($this->getSessionKey($formKey, 'step'), 0);
		$view['renderer-factory'] = $this->createRendererFactory();
		$this->getEngine()->getSession()->remove($this->getSessionKey($formKey, 'errors'));
	}

	//-- Public Methods --------------------

	/**
	 * Register the given form against the given key.  Registering the same form key twice is an error.
	 *
	 * @param string $formKey
	 * @param \Sitegear\Base\Form\FormInterface $form
	 *
	 * @return \Sitegear\Base\Form\FormInterface
	 *
	 * @throws \DomainException
	 */
	public function registerForm($formKey, FormInterface $form) {
		LoggerRegistry::debug(sprintf('FormsModule::registerForm(%s)', $formKey));
		if (isset($this->forms[$formKey])) {
			throw new \DomainException(sprintf('FormsModule cannot register form key "%s" twice', $formKey));
		}
		return $this->forms[$formKey] = $form;
	}

	/**
	 * Retrieve the form with the given key.
	 *
	 * @param string $formKey
	 * @param Request $request
	 *
	 * @return \Sitegear\Base\Form\FormInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getForm($formKey, Request $request) {
		LoggerRegistry::debug(sprintf('FormsModule::getForm(%s)', $formKey));
		if (!isset($this->forms[$formKey])) {
			$path = $this->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_SITE, $this, sprintf('%s.json', $formKey));
			if (!file_exists($path)) {
				throw new \InvalidArgumentException(sprintf('FormsModule received unknown form key "%s"', $formKey));
			}
			$formData = json_decode(file_get_contents($path), true);
			$me = $this;
			$session = $me->getEngine()->getSession();
			$valueCallback = function($name) use ($me, $session, $formKey) {
				$values = $session->get($me->getSessionKey($formKey, 'values'));
				return isset($values[$name]) ? $values[$name] : null;
			};
			$errorsCallback = function($name) use ($me, $session, $formKey) {
				$errors = $session->get($me->getSessionKey($formKey, 'errors'));
				return isset($errors[$name]) ? $errors[$name] : array();
			};
			$options = array_merge($this->config('form-builder'), array(
				'form-url' => ltrim($request->getPathInfo(), '/'),
				'submit-url' => sprintf('%s/form/%s', $this->getMountedUrl(), $formKey),
				'constraint-label-markers' => $this->config('constraints.label-markers')
			));
			$this->forms[$formKey] = $this->builder->buildForm($formData, $valueCallback, $errorsCallback, $options);
		}
		return $this->forms[$formKey];
	}

	/**
	 * Remove all current values, error messages and progress from the specified form.
	 *
	 * @param string $formKey
	 */
	public function resetForm($formKey) {
		LoggerRegistry::debug(sprintf('FormsModule::resetForm(%s)', $formKey));
		$session = $this->getEngine()->getSession();
		$session->remove($this->getSessionKey($formKey, 'step'));
		$session->remove($this->getSessionKey($formKey, 'values'));
		$session->remove($this->getSessionKey($formKey, 'errors'));
	}

	/**
	 * Validate the given set of fields against the given data.
	 *
	 * If there are any violations, store the supplied values and the error messages in the session against the
	 * relevant field names.
	 *
	 * @param string $formKey
	 * @param FieldInterface[] $fields
	 * @param array $data
	 *
	 * @return boolean|\Symfony\Component\Validator\ConstraintViolationListInterface True if the data is valid, or a
	 *   list of violations.
	 */
	public function validate($formKey, array $fields, array $data) {
		LoggerRegistry::debug(sprintf('FormsModule::validate(%s)', $formKey));
		$validator = Validation::createValidator();
		$constraints = $this->getConstraints($fields);
		$violations = $validator->validateValue($data, $constraints);
		$valid = ($violations->count() === 0);
		if (!$valid) {
			$errors = array();
			foreach ($violations as $violation) { /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
				$fieldName = trim($violation->getPropertyPath(), '[]');
				if (!isset($errors[$fieldName])) {
					$errors[$fieldName] = array();
				}
				$errors[$fieldName][] = $violation->getMessage();
			}
			$session = $this->getEngine()->getSession();
			$session->set($this->getSessionKey($formKey, 'errors'), $errors);
		}
		return $valid;
	}

	//-- Internal Methods --------------------

	/**
	 * Retrieve the session key to store a subset of data about the given form.
	 *
	 * @param string $formKey
	 * @param string $subKey
	 *
	 * @return string Session key
	 */
	protected function getSessionKey($formKey, $subKey) {
		return sprintf('forms.%s.%s', $formKey, $subKey);
	}

	/**
	 * Get the validation constraints from the given fieldset collection.
	 *
	 * @param FieldInterface[] $fields
	 *
	 * @return \Symfony\Component\Validator\Constraints\Collection
	 */
	protected function getConstraints(array $fields) {
		$constraints = array();
		foreach ($fields as $field) {
			// Store the value in the session for the next page load
			$fieldConstraints = $field->getConstraints();
			switch (sizeof($fieldConstraints)) {
				case 0:
					break;
				case 1:
					$constraints[$field->getName()] = $fieldConstraints[0];
					break;
				default:
					$constraints[$field->getName()] = $fieldConstraints;
			}
		}
		return new Collection(array(
			'fields' => $constraints,
			'allowExtraFields' => true,
			'allowMissingFields' => false
		));
	}

	/**
	 * Execute configured processors for the given step, using the configuration library to process tokens in the
	 * processor's argument defaults using the specified data.
	 *
	 * @param FormProcessorInterface $processor
	 * @param string[] $data
	 *
	 * @return array
	 */
	protected function parseProcessorArguments(FormProcessorInterface $processor, array $data) {
		$argumentsContainer = new SimpleConfigContainer($this->getConfigLoader());
		$argumentsContainer->addProcessor(new ConfigTokenProcessor($this, 'config'));
		$argumentsContainer->addProcessor(new ConfigTokenProcessor($this->getEngine(), 'engine-config'));
		$argumentsContainer->addProcessor(new ArrayTokenProcessor($data, 'data'));
		$argumentsContainer->merge($processor->getArgumentDefaults());
		return $argumentsContainer->get('');
	}

	/**
	 * Create a renderer factory.
	 *
	 * @return \Sitegear\Base\Form\Renderer\Factory\FormRendererFactoryInterface
	 */
	protected function createRendererFactory() {
		return TypeUtilities::buildTypeCheckedObject(
			$this->config('form-renderer.class'),
			'form renderer',
			null,
			'\\Sitegear\\Base\\Form\\Renderer\\Factory\\FormRendererFactoryInterface',
			$this->config('form-renderer.arguments')
		);
	}

}
