<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\Forms;

use Sitegear\Base\Form\Renderer\Factory\RendererFactoryInterface;
use Sitegear\Base\Form\StepInterface;
use Sitegear\Base\Module\AbstractUrlMountableModule;
use Sitegear\Base\Config\Processor\ArrayTokenProcessor;
use Sitegear\Base\Config\Processor\ConfigTokenProcessor;
use Sitegear\Base\Config\Container\SimpleConfigContainer;
use Sitegear\Base\Resources\ResourceLocations;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Base\Form\FormInterface;
use Sitegear\Base\Form\Field\FieldInterface;
use Sitegear\Base\Form\Processor\FormProcessorInterface;
use Sitegear\Core\Module\Forms\Form\Renderer\FormRenderer;
use Sitegear\Core\Module\Forms\Form\Builder\FormBuilder;
use Sitegear\Util\UrlUtilities;
use Sitegear\Util\NameUtilities;
use Sitegear\Util\TypeUtilities;
use Sitegear\Util\FileUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
	 * @var array Each entry is either a FormInterface implementation representing an already-built form, or an array
	 *   containing a 'type' key with value either 'definitions' or 'callback'.  Depending on the value of the 'type'
	 *   key an additional key will store the path(s) to the definition file(s) or the callback itself.
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
		$this->forms = array();
	}

	//-- AbstractUrlMountableModule Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function buildRoutes() {
		$routes = new RouteCollection();
		$routes->add('form', new Route(sprintf('%s/%s/{slug}', $this->getMountedUrl(), $this->config('routes.form')), array(), array( 'slug' => '.+' )));
		$routes->add('initialise', new Route(sprintf('%s/%s/{slug}', $this->getMountedUrl(), $this->config('routes.initialise')), array(), array( 'slug' => '.+' )));
		$routes->add('jump', new Route(sprintf('%s/%s/{slug}', $this->getMountedUrl(), $this->config('routes.jump')), array(), array( 'slug' => '.+' )));
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
	 * Reset the given form and optionally set request query (GET) parameters into the form values, then redirect to
	 * the form URL, which must be supplied as a query (GET) parameter or the home page will be used as a default.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function initialiseController(Request $request) {
		LoggerRegistry::debug('FormsModule::initialiseController()');
		$formKey = $request->attributes->get('slug');
		$this->resetForm($formKey);
		$form = $this->getForm($formKey, $request);
		$formUrl = $request->getUriForPath('/' . $request->query->get('form-url', ''));
		$data = array();
		foreach ($request->query->all() as $key => $value) {
			if ($key !== 'form-url') {
				$field = $form->getField($key);
				if (!is_null($field)) {
					$data[$key] = $value;
				}
			}
		}
		$this->setValues($formKey, $data);
		return new RedirectResponse($formUrl);
	}

	/**
	 * Handles a form submission.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 *
	 * @throws \RuntimeException
	 * @throws \OutOfBoundsException
	 */
	public function formController(Request $request) {
		LoggerRegistry::debug('FormsModule::formController()');
		// Get the form and submission details
		$formKey = $request->attributes->get('slug');
		$form = $this->getForm($formKey, $request);
		$formUrl = $request->getUriForPath('/' . $request->query->get('form-url', ''));
		$targetUrl = null;
		$response = null;
		$currentStep = $this->getCurrentStep($formKey);
		$availableSteps = $this->getAvailableSteps($formKey);
		$step = $form->getStep($currentStep);
		$fields = $step->getReferencedFields();
		$values = $form->getMethod() === 'GET' ? $request->query->all() : $request->request->all();
		$errors = null;
		$back = isset($values['back']) ? $values['back'] : false;
		unset($values['back']);
		// Set the values into the session so they can be displayed after redirecting.  Merge in with existing values
		// (e.g. from preceding steps).
		if ($back) {
			// The "back" button was clicked, try to go back a step.  No validation is necessary.
			$nextStep = $currentStep - 1;
			// Check that the previous step is not a one-way blocker.
			if (!in_array($nextStep, $availableSteps)) {
				throw new \OutOfBoundsException(sprintf('FormsModule cannot go to step %d in form "%s": step not available', $nextStep, $formKey));
			}
			// Set the values so that they will be present on the next iteration.
			$this->setValues($formKey, array_merge($this->getValues($formKey), $values));
		} else {
			// The regular submit button was clicked, try to go to the next step; run validation and processors.
			$nextStep = $currentStep + 1;
			// Validation also sets the values and errors into the session.
			$errors = $this->validateForm($formKey, $fields, $values);
			if (empty($errors)) {
				// No errors, so execute processors.
				foreach ($step->getProcessors() as $processor) {
					if (!$response instanceof Response && $processor->shouldExecute($values)) {
						$arguments = $this->parseProcessorArguments($processor, $values);
						try {
							$response = TypeUtilities::invokeCallable($processor->getProcessorMethod(), null, array( $request ), $arguments);
						} catch (\RuntimeException $exception) {
							$this->handleProcessorException($formKey, $processor, $exception);
						}
					}
				}
				// Reset the 'available steps' list if this is a one-way step.
				if ($step->isOneWay()) {
					$availableSteps = array( $nextStep );
				}
			}
		}
		// Validation passed (or was skipped) and all processors executed successfully (or were skipped).
		if (empty($errors)) {
			if ($nextStep >= $form->getStepsCount()) {
				// We're at the end of the form, and all the processors of the last step have run.  Reset the form and
				// redirect to the final target URL.
				$this->resetForm($formKey);
				if (!is_null($form->getTargetUrl())) {
					$targetUrl = $request->getUriForPath('/' . $form->getTargetUrl());
				}
			} else {
				// The form is not yet complete, so update the session.
				if (!in_array($nextStep, $availableSteps)) {
					$availableSteps[] = $nextStep;
				}
				$this->setAvailableSteps($formKey, $availableSteps);
				$this->setCurrentStep($formKey, $nextStep);
			}
		}
		// Return any of the following in order of preference: response returned by a processor method; redirection to
		// the target URL; redirection to the return URL extracted from the form URL; the form URL; the home page.
		if (!$response instanceof Response) {
			if (is_null($targetUrl) && !is_null($formUrl)) {
				$targetUrl = UrlUtilities::getReturnUrl($formUrl) ?: $formUrl;
			}
			$response = new RedirectResponse($targetUrl ?: $request->getUriForPath(''));
		}
		return $response;
	}

	/**
	 * Jump to a particular step within the given form.  This may fail if the step does not exist (out of range) or if
	 * a form rule prevents the user from jumping to the specified step (e.g. a previous step has not been completed,
	 * or an intervening step is marked one-way).
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 *
	 * @throws \OutOfBoundsException
	 */
	public function jumpController(Request $request) {
		LoggerRegistry::debug('FormsModule::jumpController()');
		// Get the form details.
		$formKey = $request->attributes->get('slug');
		$form = $this->getForm($formKey, $request);
		// Get the step being requested in the jump
		$jumpStep = intval($request->query->get('step', $this->getCurrentStep($formKey)));
		// Validation
		if ($jumpStep < 0 || $jumpStep >= $form->getStepsCount()) {
			throw new \OutOfBoundsException(sprintf('FormsModule cannot jump to step %d in form "%s": out of range', $jumpStep, $formKey));
		}
		if (!in_array($jumpStep, $this->getAvailableSteps($formKey))) {
			throw new \OutOfBoundsException(sprintf('FormsModule cannot jump to step %d in form "%s": step not available', $jumpStep, $formKey));
		}
		// Update progress and redirect back to the form URL.
		$this->setCurrentStep($formKey, $jumpStep);
		return new RedirectResponse($request->getUriForPath('/' . $request->query->get('form-url', '')));
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
		// Disable the back button if the previous step is not available.
		$currentStep = $this->getCurrentStep($formKey);
		$availableSteps = $this->getAvailableSteps($formKey);
		if (!in_array($currentStep - 1, $availableSteps) && is_array($form->getBackButtonAttributes())) {
			$form->setBackButtonAttributes(array_merge($form->getBackButtonAttributes(), array( 'disabled' => 'disabled' )));
		}
		// Setup the view.
		$this->applyConfigToView('component.form', $view);
		$view['form-renderer'] = $this->createRendererFactory()->createFormRenderer($form, $currentStep);
		// Remove errors as they are about to be displayed (they are already set in the Form structure), and we don't
		// want to show the same errors again.
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
		$view['current-step'] = $this->getCurrentStep($formKey);
		$view['available-steps'] = $this->getAvailableSteps($formKey);
		$view['jump-url-format'] = sprintf('%s/jump/%s?form-url=%s&step=%%d', $this->getMountedUrl(), $formKey, ltrim($request->getPathInfo(), '/'));
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
	public function registerFormDefinitionFilePath($formKey, $path) {
		LoggerRegistry::debug(sprintf('FormsModule::registerFormDefinitionFilePath(%s, %s)', $formKey, TypeUtilities::describe($path)));
		if (isset($this->forms[$formKey])) {
			if (!is_array($this->forms[$formKey])) {
				throw new \DomainException(sprintf('FormsModule cannot add form definition path for form key "%s", form already generated', $formKey));
			} elseif ($this->forms[$formKey]['type'] !== 'definition') {
				throw new \DomainException(sprintf('FormsModule cannot add form definition path for form key "%s", form already specified with a generator callback', $formKey));
			}
		} else {
			$this->forms[$formKey] = array(
				'type' => 'definitions',
				'paths' => array()
			);
		}
		if (is_array($path)) {
			$this->forms[$formKey]['paths'] = array_merge($this->forms[$formKey]['paths'], $path);
		} else {
			$this->forms[$formKey]['paths'][] = $path;
		}
	}

	/**
	 * Register a callback which will return a generated implementation of FormInterface.
	 *
	 * @param string $formKey
	 * @param callable|array $callback
	 *
	 * @throws \DomainException
	 */
	public function registerFormGeneratorCallback($formKey, $callback) {
		LoggerRegistry::debug(sprintf('FormsModule::registerFormGeneratorCallback(%s, ...)', $formKey));
		if (isset($this->forms[$formKey])) {
			throw new \DomainException(sprintf('FormsModule cannot add form generator callback for form key "%s", form already registered', $formKey));
		}
		$this->forms[$formKey] = array(
			'type' => 'callback',
			'callback' => $callback
		);
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
			throw new \DomainException(sprintf('FormsModule cannot register form for form key "%s", form already registered', $formKey));
		}
		return $this->forms[$formKey] = $form;
	}

	/**
	 * Retrieve the form with the given key.
	 *
	 * @param string $formKey
	 * @param Request $request
	 *
	 * @return \Sitegear\Base\Form\FormInterface|null
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getForm($formKey, Request $request) {
		LoggerRegistry::debug(sprintf('FormsModule::getForm(%s)', $formKey));
		$queryString = strlen($request->getQueryString()) > 0 ? '?' . $request->getQueryString() : '';
		$formUrl = sprintf('%s%s', ltrim($request->getPathInfo(), '/'), $queryString);
		if (!isset($this->forms[$formKey])) {
			$defaultPath = array( $this->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_SITE, $this, sprintf('%s.json', $formKey)) );
			$this->forms[$formKey] = $this->loadFormFromDefinitions($formKey, $formUrl, $defaultPath);
		} elseif (is_array($this->forms[$formKey])) {
			switch ($this->forms[$formKey]['type']) {
				case 'definitions':
					$this->forms[$formKey] = $this->loadFormFromDefinitions($formKey, $formUrl, $this->forms[$formKey]['paths']);
					break;
				case 'callback':
					$this->forms[$formKey] = call_user_func($this->forms[$formKey]['callback']);
					break;
				default: // No other values are ever assigned to 'type'
			}
		}
		return $this->forms[$formKey];
	}

	/**
	 * Get the action URL for the form with the given key.  This does not check for the existence of the specified
	 * form, it only returns the URL.
	 *
	 * @param string $formKey
	 *
	 * @return string URL relative to the site root.
	 */
	public function getFormSubmitUrl($formKey) {
		return sprintf('%s/%s/%s', $this->getMountedUrl(), $this->config('routes.form'), $formKey);
	}

	/**
	 * Remove all current values, error messages and progress from the specified form.
	 *
	 * @param string $formKey
	 */
	public function resetForm($formKey) {
		LoggerRegistry::debug(sprintf('FormsModule::resetForm(%s)', $formKey));
		$this->clearProgress($formKey);
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
	 * @param array $values
	 *
	 * @return \Symfony\Component\Validator\ConstraintViolationListInterface[] True if the data is valid, or an array
	 *   of lists of violations per field with errors.
	 */
	public function validateForm($formKey, array $fields, array $values) {
		LoggerRegistry::debug(sprintf('FormsModule::validate(%s)', $formKey));
		$validator = Validation::createValidator();
		$errors = array();
		foreach ($validator->validateValue($values, $this->getConstraints($fields)) as $violation) { /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
			$fieldName = trim($violation->getPropertyPath(), '[]');
			if (!isset($errors[$fieldName])) {
				$errors[$fieldName] = array();
			}
			$errors[$fieldName][] = $violation->getMessage();
		}
		$this->setValues($formKey, array_merge($this->getValues($formKey), $values));
		$this->setErrors($formKey, $errors);
		return $errors;
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
		return $this->getEngine()->getSession()->get($this->getSessionKey($formKey, 'values'), array());
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

	/**
	 * Get the progress data for the specified form.
	 *
	 * @param string $formKey
	 *
	 * @return integer
	 */
	protected function getCurrentStep($formKey) {
		return $this->getEngine()->getSession()->get($this->getSessionKey($formKey, 'current-step'), 0);
	}

	/**
	 * Update the progress for the specified form.
	 *
	 * @param string $formKey
	 * @param integer $currentStep
	 */
	protected function setCurrentStep($formKey, $currentStep) {
		$this->getEngine()->getSession()->set($this->getSessionKey($formKey, 'current-step'), intval($currentStep));
	}

	/**
	 * Get the progress data for the specified form.
	 *
	 * @param string $formKey
	 *
	 * @return integer[]
	 */
	protected function getAvailableSteps($formKey) {
		return $this->getEngine()->getSession()->get($this->getSessionKey($formKey, 'available-steps'), array( 0 ));
	}

	/**
	 * Update the progress for the specified form.
	 *
	 * @param string $formKey
	 * @param integer[] $availableSteps
	 */
	protected function setAvailableSteps($formKey, array $availableSteps) {
		$this->getEngine()->getSession()->set($this->getSessionKey($formKey, 'available-steps'), $availableSteps);
	}

	/**
	 * Remove any existing progress data for the given key.
	 *
	 * @param string $formKey
	 */
	protected function clearProgress($formKey) {
		$this->getEngine()->getSession()->remove($this->getSessionKey($formKey, 'current-step'));
		$this->getEngine()->getSession()->remove($this->getSessionKey($formKey, 'available-steps'));
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
	 * @param string $formKey
	 * @param string $formUrl
	 * @param string[] $paths
	 *
	 * @return FormInterface|null
	 */
	protected function loadFormFromDefinitions($formKey, $formUrl, array $paths) {
		$path = FileUtilities::firstExistingPath($paths);
		if (!empty($path)) {
			$builder = new FormBuilder($this, $formKey);
			return $builder->buildForm(array_merge(
				$this->config('form-builder'),
				array(
					'form-url' => $formUrl,
					'constraint-label-markers' => $this->config('constraints.label-markers')
				),
				json_decode(file_get_contents($path), true)
			));
		}
		return null;
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
	 * Create a RendererFactoryInterface implementation as determined by configuration.
	 *
	 * @return RendererFactoryInterface
	 */
	protected function createRendererFactory() {
		$factoryClassName = $this->config('form-renderer-factory.class-name');
		$factoryConstructorArguments = $this->config('form-renderer-factory.constructor-arguments');
		return TypeUtilities::buildTypeCheckedObject(
			$factoryClassName,
			'form renderer factory',
			null,
			array( '\\Sitegear\\Base\\Form\\Renderer\\Factory\\RendererFactoryInterface' ),
			$factoryConstructorArguments
		);
	}

}
