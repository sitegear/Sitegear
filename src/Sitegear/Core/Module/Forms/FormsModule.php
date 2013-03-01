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
use Sitegear\Base\Resources\ResourceLocations;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Base\Form\FormInterface;
use Sitegear\Base\Form\Field\FieldInterface;
use Sitegear\Base\Form\Processor\FormProcessorInterface;
use Sitegear\Core\Form\Builder\FormBuilder;
use Sitegear\Util\UrlUtilities;
use Sitegear\Util\NameUtilities;
use Sitegear\Util\TypeUtilities;
use Sitegear\Util\FileUtilities;
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
	 * @var string[][] Key-value array from form keys to array of file paths to try for the data file for that form.
	 */
	private $formDataPaths;

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
		$this->formDataPaths = array();
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
		$response = null;
		$currentStep = $request->request->get('step', 0);
		$currentStepSessionKey = $this->getSessionKey($formKey, 'step');
		$back = $request->request->get('back');
		$step = $form->getStep($currentStep);
		$fields = $step->getRootElement()->getAncestorFields();
		$data = $form->getMethod() === 'GET' ? $request->query->all() : $request->request->all();
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
					if (is_null($response)) {
						$arguments = $this->parseProcessorArguments($processor, $data);
						try {
							$response = TypeUtilities::invokeCallable($processor->getProcessorMethod(), null, array( $request ), $arguments);
						} catch (\RuntimeException $exception) {
							$this->handleProcessorException($formKey, $processor, $exception);
						}
					}
				}
			}
			// Validation passed and all processors executed successfully (or any errors were ignored).
			if ($valid) {
				// Setup for redirection to the target URL, if one is set.
				if (++$currentStep >= $form->getStepsCount()) {
					$this->resetForm($formKey);
					if (!is_null($form->getTargetUrl())) {
						$targetUrl = $request->getUriForPath('/' . $form->getTargetUrl());
					}
				} else {
					$this->getEngine()->getSession()->set($currentStepSessionKey, $currentStep);
				}
			}
		}
		// Return any of the following in order of preference: response returned by a processor method; redirection to
		// the target URL; redirection to the return URL extracted from the form URL; the form URL; the home page.
		if (is_null($response)) {
			if (is_null($targetUrl) && !is_null($formUrl)) {
				$targetUrl = UrlUtilities::getReturnUrl($formUrl) ?: $formUrl;
			}
			$response = new RedirectResponse($targetUrl ?: $request->getUriForPath(''));
		}
		return $response;
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
	 * @param array|null $values Values to set manually into the form before displaying it.  Note this is used for
	 *   rendering the form only, these values are not set into the session.
	 */
	public function formComponent(ViewInterface $view, Request $request, $formKey, array $values=null) {
		LoggerRegistry::debug('FormsModule::formComponent()');
		// Get the form and apply the specified values, if any.
		$form = $this->getForm($formKey, $request);
		if (is_array($values)) {
			foreach ($values as $name => $value) {
				$field = $form->getField($name);
				if (!is_null($field)) {
					$field->setValue($value);
				}
			}
		}
		// Setup the view.
		$this->applyConfigToView('component.form', $view);
		$view['form'] = $form;
		$view['current-step'] = $this->getEngine()->getSession()->get($this->getSessionKey($formKey, 'step'), 0);
		$view['renderer-factory'] = $this->createRendererFactory();
		// Remove errors as they are about to be displayed, and we don't want to show the same errors again.
		$this->clearErrors($formKey);
	}

	/**
	 * Display the list of steps of the specified form.  Depending on the progress within the form and the form step
	 * logic settings, some of the steps will be represented with links, and others with plain text.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param string $formKey
	 */
	public function stepsComponent(ViewInterface $view, Request $request, $formKey) {
		$this->applyConfigToView('component.steps', $view);
		$view['form'] = $this->getForm($formKey, $request);
		$view['current-step'] = $this->getEngine()->getSession()->get($this->getSessionKey($formKey, 'step'), 0);
	}

	//-- Form Management Methods --------------------

	/**
	 * Configure the specified form to load its data from the given data file.  This is usually done during the
	 * bootstrap sequence, other modules should call this method to setup their forms for potential later use.
	 *
	 * @param string $formKey
	 * @param string|string[] $path May be one path or an array of paths.
	 *
	 * @throws \DomainException
	 */
	public function addFormPath($formKey, $path) {
		LoggerRegistry::debug(sprintf('FormsModule::setFormPath(%s)', $formKey));
		if (isset($this->forms[$formKey])) {
			throw new \DomainException(sprintf('FormsModule cannot add form path for form key "%s", form already generated', $formKey));
		}
		if (!isset($this->formDataPaths[$formKey])) {
			$this->formDataPaths[$formKey] = array();
		}
		if (is_array($path)) {
			$this->formDataPaths[$formKey] = array_merge($this->formDataPaths[$formKey], $path);
		} else {
			$this->formDataPaths[$formKey][] = $path;
		}
	}

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
			// Use the first path that exists out of the paths configured with addFormPath(), plus the built-in default
			$path = FileUtilities::firstExistingPath(array_merge(
				isset($this->formDataPaths[$formKey]) ? $this->formDataPaths[$formKey] : array(),
				array( $this->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_SITE, $this, sprintf('%s.json', $formKey)) )
			));
			if (is_null($path)) {
				throw new \InvalidArgumentException(sprintf('FormsModule received form key "%s" with no available data file', $formKey));
			}
			$formData = json_decode(file_get_contents($path), true);
			$module = $this;
			$valueCallback = function($name) use ($module, $formKey) {
				$values = $module->getEngine()->getSession()->get($module->getSessionKey($formKey, 'values'));
				return isset($values[$name]) ? $values[$name] : null;
			};
			$errorsCallback = function($name) use ($module, $formKey) {
				$errors = $module->getEngine()->getSession()->get($module->getSessionKey($formKey, 'errors'));
				return isset($errors[$name]) ? $errors[$name] : array();
			};
			$query = strlen($request->getQueryString()) > 0 ? '?' . $request->getQueryString() : '';
			$options = array_merge($this->config('form-builder'), array(
				'form-url' => sprintf('%s%s', ltrim($request->getPathInfo(), '/'), $query),
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
		$this->getEngine()->getSession()->remove($this->getSessionKey($formKey, 'step'));
		$this->clearValues($formKey);
		$this->clearErrors($formKey);
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
			$this->getEngine()->getSession()->set($this->getSessionKey($formKey, 'errors'), $errors);
		}
		return $valid;
	}

	//-- Form Accessor Methods --------------------

	/**
	 * Retrieve the currently set values for the given form.
	 *
	 * @param string $formKey
	 *
	 * @return array
	 */
	public function getValues($formKey) {
		return $this->getEngine()->getSession()->get($this->getSessionKey($formKey, 'values'));
	}

	/**
	 * Manually override the form values for the given form.
	 *
	 * @param string $formKey
	 * @param array $values
	 */
	public function setValues($formKey, array $values) {
		$this->getEngine()->getSession()->set($this->getSessionKey($formKey, 'values'), $values);
	}

	/**
	 * Manually remove all form values from the given form.
	 *
	 * @param string $formKey
	 */
	public function clearValues($formKey) {
		$this->getEngine()->getSession()->remove($this->getSessionKey($formKey, 'values'));
	}

	/**
	 * Retrieve a single specified field value from the specified form.
	 *
	 * @param string $formKey
	 * @param string $fieldName
	 *
	 * @return mixed|null Value, or null if no such value is set.
	 */
	public function getFieldValue($formKey, $fieldName) {
		$values = $this->getValues($formKey);
		return isset($values[$fieldName]) ? $values[$fieldName] : null;
	}

	/**
	 * Override a single specified field value from the specified form with the given value.
	 *
	 * @param string $formKey
	 * @param string $fieldName
	 * @param mixed $value
	 */
	public function setFieldValue($formKey, $fieldName, $value) {
		$values = $this->getValues($formKey);
		$values[$fieldName] = $value;
		$this->setValues($formKey, $values);
	}

	/**
	 * Retrieve the errors currently set for the given form key.
	 *
	 * @param string $formKey
	 *
	 * @return array[]
	 */
	public function getErrors($formKey) {
		return $this->getEngine()->getSession()->get($this->getSessionKey($formKey, 'errors'));
	}

	/**
	 * Manually override the given errors for the specified form.
	 *
	 * @param string $formKey
	 * @param array $errors
	 */
	public function setErrors($formKey, array $errors) {
		$this->getEngine()->getSession()->set($this->getSessionKey($formKey, 'errors'), $errors);
	}

	/**
	 * Manually remove all errors for the specified form.
	 *
	 * @param string $formKey
	 */
	public function clearErrors($formKey) {
		$this->getEngine()->getSession()->remove($this->getSessionKey($formKey, 'errors'));
	}

	/**
	 * Get the errors, if any, for the specified field in the specified form.
	 *
	 * @param string $formKey
	 * @param string $fieldName
	 *
	 * @return string[]
	 */
	public function getFieldErrors($formKey, $fieldName) {
		$errors = $this->getErrors($formKey);
		return isset($errors[$fieldName]) ? $errors[$fieldName] : array();
	}

	/**
	 * Manually override the field errors for a single field in the specified form.
	 *
	 * @param string $formKey
	 * @param string $fieldName
	 * @param string[] $fieldErrors
	 */
	public function setFieldErrors($formKey, $fieldName, array $fieldErrors) {
		$errors = $this->getErrors($formKey);
		$errors[$fieldName] = $fieldErrors;
		$this->setErrors($formKey, $errors);
	}

	/**
	 * Add an error to the specified field in the specified form.
	 *
	 * @param string $formKey
	 * @param string $fieldName
	 * @param string $error
	 */
	public function addFieldError($formKey, $fieldName, $error) {
		$errors = $this->getErrors($formKey);
		if (!isset($errors[$fieldName])) {
			$errors[$fieldName] = array();
		}
		$errors[$fieldName][] = $error;
		$this->setErrors($formKey, $errors);
	}

	/**
	 * Manually remove all errors from the specified field in the specified form.
	 *
	 * @param string $formKey
	 * @param string $fieldName
	 */
	public function clearFieldErrors($formKey, $fieldName) {
		$errors = $this->getErrors($formKey);
		unset($errors[$fieldName]);
		$this->setErrors($formKey, $errors);
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
	 * Handle the given exception, which was raised by the given processor.
	 *
	 * @param string $formKey
	 * @param \Sitegear\Base\Form\Processor\FormProcessorInterface $processor
	 * @param \RuntimeException $exception
	 *
	 * @return boolean
	 *
	 * @throws \RuntimeException
	 */
	protected function handleProcessorException($formKey, FormProcessorInterface $processor, \RuntimeException $exception) {
		$result = true;
		foreach ($processor->getExceptionFieldNames() as $fieldName) {
			$this->addFieldError($formKey, $fieldName, $exception->getMessage());
		}
		switch ($processor->getExceptionAction()) {
			case FormProcessorInterface::EXCEPTION_ACTION_RETHROW:
				throw $exception;
				break;
			case FormProcessorInterface::EXCEPTION_ACTION_FAIL:
				$result = false;
				break;
			case FormProcessorInterface::EXCEPTION_ACTION_IGNORE:
				break;
		}
		return $result;
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
