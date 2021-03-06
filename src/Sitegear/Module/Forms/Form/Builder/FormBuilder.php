<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Forms\Form\Builder;

use Sitegear\Form\Form;
use Sitegear\Form\FormInterface;
use Sitegear\Form\Step;
use Sitegear\Form\StepInterface;
use Sitegear\Form\FieldReference;
use Sitegear\Form\Fieldset;
use Sitegear\Form\Constraint\ConditionalConstraint;
use Sitegear\Form\Constraint\ConditionalConstraintInterface;
use Sitegear\Form\Condition\ConditionInterface;
use Sitegear\Form\Processor\ModuleProcessor;
use Sitegear\Util\ArrayUtilities;
use Sitegear\Util\NameUtilities;
use Sitegear\Util\LoggerRegistry;
use Sitegear\Util\TypeUtilities;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;

/**
 * Core FormBuilderInterface implementation.  Maps the format defined by the `FormsModule` data files into the Form
 * objects.
 */
class FormBuilder extends AbstractFormsModuleFormBuilder {

	//-- FormBuilderInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function buildForm($formDefinition) {
		LoggerRegistry::debug('FormBuilder::buildForm()');
		$form = new Form(
			$this->getSubmitUrl(isset($formDefinition['form-url']) ? $formDefinition['form-url'] : null),
			isset($formDefinition['target-url']) ? $formDefinition['target-url'] : null,
			isset($formDefinition['cancel-url']) ? $formDefinition['cancel-url'] : null,
			isset($formDefinition['method']) ? $formDefinition['method'] : null,
			$this->getFormsModule()->registry()->getValues($this->getFormKey()),
			$this->getFormsModule()->registry()->getErrors($this->getFormKey()),
			isset($formDefinition['submit-button']) ? $formDefinition['submit-button'] : null,
			isset($formDefinition['reset-button']) ? $formDefinition['reset-button'] : null,
			isset($formDefinition['back-button']) ? $formDefinition['back-button'] : null
		);
		$constraintLabelMarkers = isset($formDefinition['constraint-label-markers']) ? $formDefinition['constraint-label-markers'] : array();
		foreach ($formDefinition['fields'] as $name => $fieldData) {
			$form->addField($this->buildField($form, $name, $fieldData, $constraintLabelMarkers));
		}
		for ($i=0, $l=sizeof($formDefinition['steps']); $i<$l; ++$i) {
			$form->addStep($this->buildStep($form, $formDefinition, $i));
		}
		return $form;
	}

	//-- Public Methods --------------------

	/**
	 * Create a single field.
	 *
	 * @param FormInterface $form
	 * @param string $name
	 * @param array $fieldDefinition
	 * @param string[] $constraintLabelMarkers
	 *
	 * @return \Sitegear\Form\Field\FieldInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	public function buildField(FormInterface $form, $name, array $fieldDefinition, array $constraintLabelMarkers=null) {
		LoggerRegistry::debug('FormBuilder::buildField()');
		// Get the class for the field type, this is either a directly specified fully specified class name, or a type
		// value which is appended to a matching registered namespace.
		$fieldTypeClass = null;
		if (isset($fieldDefinition['class'])) {
			if (class_exists($fieldDefinition['class'])) {
				$fieldTypeClass = new \ReflectionClass($fieldDefinition['class']);
			}
		} else {
			$fieldTypeClass = TypeUtilities::firstExistingClass(
				$this->getFormsModule()->registry()->getFieldNamespaces(),
				NameUtilities::convertToStudlyCaps($fieldDefinition['type']) . 'Field'
			);
		}
		if (is_null($fieldTypeClass)) {
			throw new \InvalidArgumentException(sprintf('FormBuilder could not find a field class for the name "%s"', $fieldDefinition['type']));
		}
		// Get label text and markers.
		$labelText = isset($fieldDefinition['label']) ? $fieldDefinition['label'] : '';
		$labelMarkers = array();
		if (isset($fieldDefinition['label-markers'])) {
			if (is_array($fieldDefinition['label-markers'])) {
				$labelMarkers = array_merge($labelMarkers, $fieldDefinition['label-markers']);
			} else {
				$labelMarkers[] = $fieldDefinition['label-markers'];
			}
		}
		// Get constraints.
		$conditionalConstraints = array();
		if (isset($fieldDefinition['constraints'])) {
			if (is_null($constraintLabelMarkers)) {
				$constraintLabelMarkers = array();
			}
			foreach ($fieldDefinition['constraints'] as $constraintDefinition) {
				$conditionalConstraints[] = $this->buildConditionalConstraint($constraintDefinition);
				if (isset($constraintDefinition['name']) && isset($constraintLabelMarkers[$constraintDefinition['name']])) {
					if (is_array($constraintLabelMarkers[$constraintDefinition['name']])) {
						$labelMarkers = array_merge($labelMarkers, $constraintLabelMarkers[$constraintDefinition['name']]);
					} else {
						$labelMarkers[] = $constraintLabelMarkers[$constraintDefinition['name']];
					}
				}
			}
		}
		// Get conditions.
		$includeConditions = array();
		if (isset($fieldDefinition['conditions'])) {
			foreach ($fieldDefinition['conditions'] as $conditionDefinition) {
				$includeConditions[] = $this->buildCondition($conditionDefinition);
			}
		}
		// Create the field instance.
		$defaultValue = isset($fieldDefinition['default']) ? $fieldDefinition['default'] : null;
		$settings = isset($fieldDefinition['settings']) ? $fieldDefinition['settings'] : array();
		return $fieldTypeClass->newInstance($form, $name, $defaultValue, $labelText, $labelMarkers, $conditionalConstraints, $includeConditions, $settings);
	}

	/**
	 * Create a single constraint on a field.
	 *
	 * @param array $constraintDefinition
	 *
	 * @return ConditionalConstraintInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	public function buildConditionalConstraint(array $constraintDefinition) {
		LoggerRegistry::debug('FormBuilder::buildConstraint()');
		/** @var Constraint $constraint */
		if (isset($constraintDefinition['class'])) {
			// Constraint class is directly set in the definition.
			$constraint = new $constraintDefinition['class']($constraintDefinition['options']);
		} elseif (isset($constraintDefinition['callback'])) {
			// Callback constraint which calls a method in either the engine or a module.
			// Check the 'callback' value first, it should be either 'engine' or 'module'; in the latter case a
			// 'module' key is also expected.
			switch ($constraintDefinition['callback']) {
				case 'engine':
					$callbackObject = $this->getFormsModule()->getEngine();
					break;
				case 'module':
					$callbackObject = $this->getFormsModule()->getEngine()->getModule($constraintDefinition['module']);
					break;
				default:
					throw new \InvalidArgumentException(sprintf('FormBuilder encountered invalid callback type "%s"', $constraintDefinition['callback']));
			}
			// Setup the required options and return the Callback constraint object.
			if (!isset($constraintDefinition['options'])) {
				$constraintDefinition['options'] = array();
			}
			if (!isset($constraintDefinition['options']['methods'])) {
				$constraintDefinition['options']['methods'] = array();
			}
			$constraintDefinition['options']['methods'][] = array( $callbackObject, NameUtilities::convertToCamelCase($constraintDefinition['method']) );
			$constraint = new Callback($constraintDefinition['options']);
		} else {
			// Use the registered constraint class mappings.
			$constraintClass = TypeUtilities::firstExistingClass(
				$this->getFormsModule()->registry()->getConstraintNamespaces(),
				NameUtilities::convertToStudlyCaps($constraintDefinition['name'])
			);
			if (is_null($constraintClass)) {
				throw new \InvalidArgumentException(sprintf('FormBuilder could not find a constraint class for the name "%s"', $constraintDefinition['name']));
			}
			$constraint = $constraintClass->newInstance(isset($constraintDefinition['options']) ? $constraintDefinition['options'] : null);
		}
		// Add conditions to the constraint if any are specified.
		$conditions = array();
		if (isset($constraintDefinition['conditions'])) {
			foreach ($constraintDefinition['conditions'] as $conditionDefinition) {
				$conditions[] = $this->buildCondition($conditionDefinition);
			}
		}
		return new ConditionalConstraint($constraint, $conditions);
	}

	/**
	 * Create a single step of the form.
	 *
	 * @param FormInterface $form
	 * @param array $formDefinition
	 * @param integer $stepIndex
	 *
	 * @return \Sitegear\Form\StepInterface
	 */
	public function buildStep(FormInterface $form, array $formDefinition, $stepIndex) {
		LoggerRegistry::debug('FormBuilder::buildStep()');
		$stepDefinition = $formDefinition['steps'][$stepIndex];
		$oneWay = isset($stepDefinition['one-way']) ? $stepDefinition['one-way'] : false;
		$heading = isset($stepDefinition['heading']) ? $stepDefinition['heading'] : null;
		$errorHeading = isset($stepDefinition['error-heading']) ? $stepDefinition['error-heading'] : null;
		$step = new Step($form, $stepIndex, $oneWay, $heading, $errorHeading);
		if (isset($stepDefinition['fieldsets'])) {
			foreach ($stepDefinition['fieldsets'] as $fieldsetDefinition) {
				$step->addFieldset($this->buildFieldset($step, $fieldsetDefinition));
			}
		}
		if (isset($stepDefinition['processors'])) {
			foreach ($stepDefinition['processors'] as $processorDefinition) {
				$step->addProcessor($this->buildProcessor($processorDefinition));
			}
		}
		return $step;
	}

	/**
	 * Create a single fieldset, which exists within a given step.
	 *
	 * @param \Sitegear\Form\StepInterface $step
	 * @param array $fieldsetDefinition
	 *
	 * @return \Sitegear\Form\Fieldset
	 */
	public function buildFieldset(StepInterface $step, array $fieldsetDefinition) {
		LoggerRegistry::debug('FormBuilder::buildFieldset()');
		$heading = isset($fieldsetDefinition['heading']) ? $fieldsetDefinition['heading'] : null;
		$fieldset = new Fieldset($step, $heading);
		foreach ($fieldsetDefinition['fields'] as $fieldDefinition) {
			if (!is_array($fieldDefinition)) {
				$fieldDefinition = array( 'field' => $fieldDefinition );
			}
			$fieldset->addFieldReference(new FieldReference(
				$fieldDefinition['field'],
				isset($fieldDefinition['read-only']) && $fieldDefinition['read-only'],
				!isset($fieldDefinition['wrapped']) || $fieldDefinition['wrapped']
			));
		}
		return $fieldset;
	}

	/**
	 * Create a single processor for a step of the form.
	 *
	 * @param array $processorDefinition
	 *
	 * @return \Sitegear\Form\Processor\FormProcessorInterface
	 */
	public function buildProcessor(array $processorDefinition) {
		LoggerRegistry::debug('FormBuilder::buildProcessor()');
		$processor = new ModuleProcessor(
			$this->getFormsModule()->getEngine()->getModule($processorDefinition['module']),
			$processorDefinition['method'],
			isset($processorDefinition['arguments']) ? $processorDefinition['arguments'] : array(),
			isset($processorDefinition['exception-action']) ? $processorDefinition['exception-action'] : null
		);
		if (isset($processorDefinition['conditions'])) {
			foreach ($processorDefinition['conditions'] as $conditionDefinition) {
				$processor->addCondition($this->buildCondition($conditionDefinition));
			}
		}
		return $processor;
	}

	/**
	 * Create a single condition for a single processor.
	 *
	 * @param array $conditionDefinition
	 *
	 * @return ConditionInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	public function buildCondition(array $conditionDefinition) {
		$conditionClass = TypeUtilities::firstExistingClass(
			$this->getFormsModule()->registry()->getConditionNamespaces(),
			NameUtilities::convertToStudlyCaps($conditionDefinition['condition']) . 'Condition'
		);
		if (is_null($conditionClass)) {
			throw new \InvalidArgumentException(sprintf('FormBuilder could not find a condition class for the name "%s"', $conditionDefinition['condition']));
		}
		return $conditionClass->newInstance($conditionDefinition['options']);
	}

}
