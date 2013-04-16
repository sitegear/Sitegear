<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Forms;

use Sitegear\Config\ConfigurableInterface;
use Sitegear\Config\Configuration;
use Sitegear\Config\Processor\ArrayTokenProcessor;
use Sitegear\Config\Processor\ConfigTokenProcessor;
use Sitegear\Config\Processor\EngineTokenProcessor;
use Sitegear\Form\Field\FieldInterface;
use Sitegear\Form\FormInterface;
use Sitegear\Module\ModuleInterface;
use Sitegear\Info\ResourceLocations;
use Sitegear\Module\Forms\Form\Builder\FormBuilder;
use Sitegear\Util\FileUtilities;
use Sitegear\Util\LoggerRegistry;
use Sitegear\Util\TypeUtilities;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validation;

class FormRegistry {

	//-- Attributes --------------------

	/**
	 * @var array[] Each entry is a key-value array, which ultimately contains a 'form' key that refers to a
	 *   FormInterface implementation.  For forms that are defined but not built, the 'type' key should have a value of
	 *   either 'definitions' or 'callback'.  Depending on the value of the 'type' key an additional key will store the
	 *   path(s) to the definition file(s) or a callback.
	 */
	private $forms;

	/**
	 * @var FormsModule
	 */
	private $formsModule;

	/**
	 * @var array
	 */
	private $baseConfig;

	/**
	 * @var string[] Array of namespaces containing field classes.  Field class names are suffixed with "Field".
	 */
	private $fieldNamespaces;

	/**
	 * @var string[] Array of namespaces containing constraint classes.
	 */
	private $constraintNamespaces;

	/**
	 * @var string[] Array of namespaces containing condition classes.  Condition class names are suffixed with
	 *   "Condition".
	 */
	private $conditionNamespaces;

	//-- Constructor --------------------

	/**
	 * @param FormsModule $formsModule
	 * @param array $baseConfig
	 * @param string[] $fieldNamespaces
	 * @param string[] $constraintNamespaces
	 * @param string[] $conditionNamespaces
	 */
	public function __construct(FormsModule $formsModule, array $baseConfig, array $fieldNamespaces=null, array $constraintNamespaces=null, array $conditionNamespaces=null) {
		$this->forms = array();
		$this->formsModule = $formsModule;
		$this->baseConfig = $baseConfig;
		$this->fieldNamespaces = $fieldNamespaces ?: array();
		$this->constraintNamespaces = $constraintNamespaces ?: array();
		$this->conditionNamespaces = $conditionNamespaces ?: array();
	}

	//-- Public Methods --------------------

	/**
	 * Configure the specified form to load its data from the given data file relative to the given module.  This is
	 * usually done during the bootstrap sequence, other modules should call this method to setup their forms for
	 * potential later use.
	 *
	 * @param string $formKey
	 * @param ModuleInterface $module
	 * @param string|string[] $path May be one path or an array of paths.
	 *
	 * @throws \DomainException
	 */
	public function registerFormDefinitionFilePath($formKey, ModuleInterface $module, $path) {
		LoggerRegistry::debug(sprintf('FormRegistry::registerFormDefinitionFilePath(%s, %s)', $formKey, TypeUtilities::describe($path)));
		if (isset($this->forms[$formKey])) {
			if (isset($this->forms[$formKey]['form'])) {
				throw new \DomainException(sprintf('FormRegistry cannot add form definition path for form key "%s", form already generated', $formKey));
			} elseif ($this->forms[$formKey]['type'] !== 'definition') {
				throw new \DomainException(sprintf('FormRegistry cannot add form definition path for form key "%s", form already specified with a generator callback', $formKey));
			}
		} else {
			$this->forms[$formKey] = array(
				'type' => 'definitions',
				'module' => $module,
				'path' => $path
			);
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
		LoggerRegistry::debug(sprintf('FormRegistry::registerFormGeneratorCallback(%s, ...)', $formKey));
		if (isset($this->forms[$formKey])) {
			throw new \DomainException(sprintf('FormRegistry cannot add form generator callback for form key "%s", form already registered', $formKey));
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
	 * @param \Sitegear\Form\FormInterface $form
	 *
	 * @return \Sitegear\Form\FormInterface
	 *
	 * @throws \DomainException
	 */
	public function registerForm($formKey, FormInterface $form) {
		LoggerRegistry::debug(sprintf('FormRegistry::registerForm(%s)', $formKey));
		if (isset($this->forms[$formKey])) {
			throw new \DomainException(sprintf('FormRegistry cannot register form for form key "%s", form already registered', $formKey));
		}
		return $this->forms[$formKey] = array( 'form' => $form );
	}

	/**
	 * Retrieve the form with the given key.
	 *
	 * @param string $formKey
	 * @param Request $request
	 *
	 * @return \Sitegear\Form\FormInterface|null
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getForm($formKey, Request $request) {
		LoggerRegistry::debug(sprintf('FormRegistry::getForm(%s)', $formKey));
		$formUrl = $request->getUri();
		$siteInfo = $this->formsModule->getEngine()->getSiteInfo();
		if (!isset($this->forms[$formKey])) {
			$this->forms[$formKey] = array(
				'form' => $this->loadFormFromDefinitions($formKey, $formUrl, $this->formsModule, array(
					$siteInfo->getSitePath(ResourceLocations::RESOURCE_LOCATION_SITE, sprintf('%s.json', $formKey), $this->formsModule)
				))
			);
		} elseif (is_array($this->forms[$formKey]) && !isset($this->forms[$formKey]['form'])) {
			switch ($this->forms[$formKey]['type']) {
				case 'definitions':
					$module = $this->forms[$formKey]['module'];
					$this->forms[$formKey]['form'] = $this->loadFormFromDefinitions($formKey, $formUrl, $module, array(
						$siteInfo->getSitePath(ResourceLocations::RESOURCE_LOCATION_SITE, $this->forms[$formKey]['path'], $module),
						$siteInfo->getSitePath(ResourceLocations::RESOURCE_LOCATION_MODULE, $this->forms[$formKey]['path'], $module)
					));
					break;
				case 'callback':
					$this->forms[$formKey]['form'] = call_user_func($this->forms[$formKey]['callback']);
					break;
				default: // No other values are ever assigned to 'type'
			}
		}
		if (!isset($this->forms[$formKey]['form'])) {
			throw new \InvalidArgumentException(sprintf('FormRegistry cannot retrieve form with unknown key "%s"', $formKey));
		}
		return $this->forms[$formKey]['form'];
	}

	//-- Form Generation Control Methods --------------------

	/**
	 * Register a format string for the fully qualified class name of field classes, which may contain the token %name%
	 * which is replaced by the name of the field in studly caps form.
	 *
	 * @param string $format
	 */
	public function registerFieldNamespace($format) {
		$this->fieldNamespaces[] = $format;
	}

	/**
	 * Get the list of registered class name formats for field objects.
	 *
	 * @return string[]
	 */
	public function getFieldNamespaces() {
		return $this->fieldNamespaces;
	}

	/**
	 * Register a format string for the fully qualified class name of constraint classes, which may contain the token
	 * %name% which is replaced by the name of the constraint in studly caps form.
	 *
	 * @param string $namespace
	 */
	public function registerConstraintNamespace($namespace) {
		$this->constraintNamespaces[] = $namespace;
	}

	/**
	 * Get the list of registered class name formats for constraint objects.
	 *
	 * @return string[]
	 */
	public function getConstraintNamespaces() {
		return $this->constraintNamespaces;
	}

	/**
	 * Register a format string for the fully qualified class name of condition classes, which may contain the token
	 * %name% which is replaced by the name of the condition in studly caps form.
	 *
	 * @param string $format
	 */
	public function registerConditionNamespace($format) {
		$this->conditionNamespaces[] = $format;
	}

	/**
	 * Get the list of registered class name formats for condition objects.
	 *
	 * @return string[]
	 */
	public function getConditionNamespaces() {
		return $this->conditionNamespaces;
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
		LoggerRegistry::debug(sprintf('FormRegistry::validate(%s)', $formKey));
		$validator = Validation::createValidator();
		$errors = array();
		foreach ($validator->validateValue($values, $this->getConstraints($fields, $values)) as $violation) {
			/** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
			$fieldName = trim($violation->getPropertyPath(), '[]');
			if (!isset($errors[$fieldName])) {
				$errors[$fieldName] = array();
			}
			$errors[$fieldName][] = $violation->getMessage();
		}
		$this->setErrors($formKey, $errors);
		return $errors;
	}

	/**
	 * Remove all current values, error messages and progress from the specified form.
	 *
	 * @param string $formKey
	 */
	public function resetForm($formKey) {
		LoggerRegistry::debug(sprintf('FormRegistry::resetForm(%s)', $formKey));
		$this->clearProgress($formKey);
		$this->clearValues($formKey);
		$this->clearErrors($formKey);
	}

	/**
	 * Retrieve the currently set values for the given form.
	 *
	 * @param string $formKey
	 *
	 * @return array
	 */
	public function getValues($formKey) {
		return $this->getSession()->get($this->getSessionKey($formKey, 'values'), array());
	}

	/**
	 * Manually override the form values for the given form.
	 *
	 * @param string $formKey
	 * @param array $values
	 */
	public function setValues($formKey, array $values) {
		$this->getSession()->set($this->getSessionKey($formKey, 'values'), $values);
	}

	/**
	 * Manually remove all form values from the given form.
	 *
	 * @param string $formKey
	 */
	public function clearValues($formKey) {
		$this->getSession()->remove($this->getSessionKey($formKey, 'values'));
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
		return $this->getSession()->get($this->getSessionKey($formKey, 'errors'), array());
	}

	/**
	 * Manually override the given errors for the specified form.
	 *
	 * @param string $formKey
	 * @param array $errors
	 */
	public function setErrors($formKey, array $errors) {
		$this->getSession()->set($this->getSessionKey($formKey, 'errors'), $errors);
	}

	/**
	 * Manually remove all errors for the specified form.
	 *
	 * @param string $formKey
	 */
	public function clearErrors($formKey) {
		$this->getSession()->remove($this->getSessionKey($formKey, 'errors'));
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
	public function getCurrentStep($formKey) {
		return $this->getSession()->get($this->getSessionKey($formKey, 'current-step'), 0);
	}

	/**
	 * Update the progress for the specified form.
	 *
	 * @param string $formKey
	 * @param integer $currentStep
	 */
	public function setCurrentStep($formKey, $currentStep) {
		$this->getSession()->set($this->getSessionKey($formKey, 'current-step'), intval($currentStep));
	}

	/**
	 * Get the progress data for the specified form.
	 *
	 * @param string $formKey
	 *
	 * @return integer[]
	 */
	public function getAvailableSteps($formKey) {
		return $this->getSession()->get($this->getSessionKey($formKey, 'available-steps'), array( 0 ));
	}

	/**
	 * Update the progress for the specified form.
	 *
	 * @param string $formKey
	 * @param integer[] $availableSteps
	 */
	public function setAvailableSteps($formKey, array $availableSteps) {
		$this->getSession()->set($this->getSessionKey($formKey, 'available-steps'), $availableSteps);
	}

	/**
	 * Remove any existing progress data for the given key.
	 *
	 * @param string $formKey
	 */
	public function clearProgress($formKey) {
		$this->getSession()->remove($this->getSessionKey($formKey, 'current-step'));
		$this->getSession()->remove($this->getSessionKey($formKey, 'available-steps'));
	}

	//-- Internal Methods --------------------

	/**
	 * Shortcut method to retrieve the Session implementation from the engine where this registry is running.
	 *
	 * @return null|\Symfony\Component\HttpFoundation\Session\SessionInterface
	 */
	protected function getSession() {
		return $this->formsModule->getEngine()->getSession();
	}

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
	 * @param ConfigurableInterface $module
	 * @param string[] $paths
	 *
	 * @return FormInterface|null
	 */
	protected function loadFormFromDefinitions($formKey, $formUrl, ConfigurableInterface $module, array $paths) {
		$path = FileUtilities::firstExistingPath($paths);
		if (!empty($path)) {
			// Setup the configuration container for the form definition.
			$config = new Configuration($this->formsModule->getConfigLoader());
			$config->addProcessor(new ArrayTokenProcessor($this->getValues($formKey), 'data'));
			$config->addProcessor(new EngineTokenProcessor($this->formsModule->getEngine(), 'engine'));
			$config->addProcessor(new ConfigTokenProcessor($this->formsModule->getEngine(), 'engine-config'));
			$config->addProcessor(new ConfigTokenProcessor($module, 'config'));
			// Merge the configuration defaults and form definition file contents.
			$config->merge($this->baseConfig);
			$config->merge(array( 'form-url' => $formUrl ));
			$config->merge($path);
			// Build and return the form
			$builder = new FormBuilder($this->formsModule, $formKey);
			return $builder->buildForm($config->all());
		}
		return null;
	}

	/**
	 * Get the validation constraints from the given fieldset collection.
	 *
	 * @param FieldInterface[] $fields
	 * @param array $values
	 *
	 * @return \Symfony\Component\Validator\Constraints\Collection
	 */
	protected function getConstraints(array $fields, array $values) {
		$constraints = array();
		foreach ($fields as $field) {
			$fieldConditionalConstraints = $field->getConditionalConstraints();
			$fieldConstraints = array();
			foreach ($fieldConditionalConstraints as $fieldConditionalConstraint) {
				if ($fieldConditionalConstraint->shouldApplyConstraint($values)) {
					$fieldConstraints[] = $fieldConditionalConstraint->getConstraint();
				}
			}
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

}
