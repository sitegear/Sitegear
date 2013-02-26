<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Forms;

use Sitegear\Base\Module\AbstractUrlMountableModule;
use Sitegear\Base\Config\Processor\ArrayTokenProcessor;
use Sitegear\Base\Config\Processor\ConfigTokenProcessor;
use Sitegear\Base\Config\Container\SimpleConfigContainer;
use Sitegear\Base\Engine\EngineInterface;
use Sitegear\Base\Resources\ResourceLocations;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Base\Form\FormInterface;
use Sitegear\Base\Form\Field\FieldInterface;
use Sitegear\Base\Form\Renderer\Factory\NamespaceFormRendererFactory;
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

	//-- Constructor --------------------

	public function __construct(EngineInterface $engine) {
		parent::__construct($engine);
		$this->builder = new FormBuilder($engine);
		$this->forms = array();
	}

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Web Forms';
	}

	//-- AbstractUrlMountableModule Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function buildRoutes() {
		$routes = new RouteCollection();
		$routes->add('form', new Route(sprintf('%s/{slug}', $this->getMountedUrl())));
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
		LoggerRegistry::debug('FormsModule::formController');

		// Get the form and submission details
		$formKey = $request->attributes->get('slug');
		$form = $this->getForm($formKey, $request);
		$currentStep = 0; // TODO Multiple steps
		$data = $request->request->all();

		// Perform validation, and execute processors if no errors occur
		$step = $form->getStep($currentStep);
		$fields = $step->getRootElement()->getAncestorFields();
		if ($valid = $this->validate($formKey, $fields, $data)) {
			foreach ($step->getProcessors() as $processor) {
				$arguments = $this->parseProcessorArguments($processor, $data);
				TypeUtilities::invokeCallable($processor->getCallable(), null, array( $request ), $arguments);
			}
		}

		// Redirect to the target URL, which is either the actual target-url, if the form is finished and there is one
		// set, or the form-url GET parameter, if one is set (which it should be), or to the home page as a fallback.
		$finished = $valid && !is_null($form->getTargetUrl()) && (++$currentStep >= $form->getStepsCount());
		$targetUrl = $finished ? $request->getUriForPath('/' . $form->getTargetUrl()) : $request->query->get('form-url', $request->getUriForPath(''));
		return new RedirectResponse($targetUrl);
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
		LoggerRegistry::debug('FormsModule::formComponent');
		$view['form'] = $this->getForm($formKey, $request);
		$view['current-step'] = 0; // TODO Multiple steps
		$view['renderer-factory'] = new NamespaceFormRendererFactory(); // TODO Configurable renderer factory??
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
		if (!isset($this->forms[$formKey])) {
			$path = $this->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_SITE, $this, sprintf('%s.json', $formKey));
			if (!file_exists($path)) {
				throw new \InvalidArgumentException(sprintf('FormsModule received unknown form key "%s"', $formKey));
			}
			$formData = json_decode(file_get_contents($path), true);
			$module = $this;
			$session = $module->getEngine()->getSession();
			$valueCallback = function($name) use ($module, $session, $formKey) {
				$sessionKey = $module->getSessionKey($formKey, $name, 'value');
				$value = $session->get($sessionKey);
				$session->remove($sessionKey);
				return $value;
			};
			$errorsCallback = function($name) use ($module, $session, $formKey) {
				$sessionKey = $module->getSessionKey($formKey, $name, 'errors');
				$value = $session->get($sessionKey);
				$session->remove($sessionKey);
				return $value;
			};
			$options = array_merge($this->config('form-builder'), array(
				'form-url' => $request->getUri(),
				'submit-url' => sprintf('%s/%s', $this->getMountedUrl(), $formKey),
				'constraint-label-markers' => $this->config('constraints.label-markers'),
			));
			$this->forms[$formKey] = $this->builder->buildForm($formData, $valueCallback, $errorsCallback, $options);
		}
		return $this->forms[$formKey];
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
	 * @return boolean Whether or not the given data is valid.
	 */
	public function validate($formKey, array $fields, array $data) {
		$validator = Validation::createValidator();
		$constraints = $this->getConstraints($fields);
		$violations = $validator->validateValue($data, $constraints);
		$valid = ($violations->count() === 0);
		if (!$valid) {
			// An error occurred.
			$session = $this->getEngine()->getSession();

			// Set the values into the session so they can be displayed after redirecting.
			foreach ($fields as $field) {
				$session->set($this->getSessionKey($formKey, $field->getName(), 'value'), $data[$field->getName()]);
			}

			// Set the error messages into the session so they can be displayed after redirecting.
			$errors = array();
			foreach ($violations as $violation) { /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
				$field = trim($violation->getPropertyPath(), '[]');
				if (!isset($errors[$field])) {
					$errors[$field] = array();
				}
				$errors[$field][] = $violation->getMessage();
			}
			foreach ($errors as $field => $fieldErrors) {
				$session->set($this->getSessionKey($formKey, $field, 'errors'), $fieldErrors);
				LoggerRegistry::debug(sprintf('Set validation violation for field "%s"; got %d error messages', $field, sizeof($fieldErrors)));
			}
		}
		return $valid;
	}

	//-- Internal Methods --------------------

	/**
	 * Retrieve the session key to store details about the given field in the given form.
	 *
	 * @param string $formKey
	 * @param string $fieldName
	 * @param string $subKey
	 *
	 * @return string Session key
	 */
	protected function getSessionKey($formKey, $fieldName, $subKey) {
		return sprintf('forms.%s.%s.%s', $formKey, $fieldName, $subKey);
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

}
