<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\Forms\Form\Builder;

use Sitegear\Base\Form\Form;
use Sitegear\Base\Form\FormInterface;
use Sitegear\Base\Form\Processor\Condition\ConditionInterface;
use Sitegear\Base\Form\Step;
use Sitegear\Base\Form\StepInterface;
use Sitegear\Base\Form\FieldReference;
use Sitegear\Base\Form\Fieldset;
use Sitegear\Base\Form\Processor\ModuleProcessor;
use Sitegear\Util\ArrayUtilities;
use Sitegear\Util\NameUtilities;
use Sitegear\Util\UrlUtilities;
use Sitegear\Util\LoggerRegistry;

/**
 * Core FormBuilderInterface implementation.  Maps the format defined by the `FormsModule` data files into the Form
 * objects.
 */
class FormBuilder extends AbstractFormsModuleFormBuilder {

	//-- Constants --------------------

	/**
	 * Default class name for FieldInterface implementations.
	 */
	const CLASS_NAME_FORMAT_FIELD = '\\Sitegear\\Base\\Form\\Field\\%sField';

	/**
	 * Default class name for Constraint implementations.
	 */
	const CLASS_NAME_FORMAT_CONSTRAINT = '\\Symfony\\Component\\Validator\\Constraints\\%s';

	/**
	 * Default class name for Constraint implementations.
	 */
	const CLASS_NAME_FORMAT_CONDITION = '\\Sitegear\\Base\\Form\\Processor\\Condition\\%sCondition';

	//-- FormBuilderInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function buildForm($formData) {
		LoggerRegistry::debug('FormBuilder::buildForm()');
		$submitUrl = UrlUtilities::generateLinkWithReturnUrl(
			$formData['submit-url'],
			$formData['form-url'],
			'form-url'
		);
		$form = new Form(
			$submitUrl,
			isset($formData['target-url']) ? $formData['target-url'] : null,
			isset($formData['cancel-url']) ? $formData['cancel-url'] : null,
			isset($formData['method']) ? $formData['method'] : null,
			isset($formData['submit-button']) ? $formData['submit-button'] : null,
			isset($formData['reset-button']) ? $formData['reset-button'] : null,
			isset($formData['back-button']) ? $formData['back-button'] : null
		);
		$constraintLabelMarkers = isset($formData['constraint-label-markers']) ? $formData['constraint-label-markers'] : array();
		foreach ($formData['fields'] as $name => $fieldData) {
			$form->addField($this->buildField($name, $fieldData, $constraintLabelMarkers));
		}
		for ($i=0, $l=sizeof($formData['steps']); $i<$l; ++$i) {
			$form->addStep($this->buildStep($form, $formData, $i));
		}
		return $form;
	}

	//-- Public Methods --------------------

	/**
	 * Create a single field.
	 *
	 * @param string $name
	 * @param array $fieldData
	 * @param string[] $constraintLabelMarkers
	 *
	 * @return \Sitegear\Base\Form\Field\FieldInterface
	 */
	public function buildField($name, array $fieldData, array $constraintLabelMarkers=null) {
		LoggerRegistry::debug('FormBuilder::buildField()');
		$fieldType = $fieldData['type'];
		$fieldTypeClass = new \ReflectionClass(
			isset($fieldData['class']) ?
					$fieldData['class'] :
					sprintf(self::CLASS_NAME_FORMAT_FIELD, NameUtilities::convertToStudlyCaps($fieldType))
		);
		$defaultValue = isset($fieldData['default']) ? $fieldData['default'] : null;
		$labelText = isset($fieldData['label']) ? $fieldData['label'] : '';
		$labelMarkers = array();
		$constraints = array();
		if (isset($fieldData['constraints'])) {
			if (is_null($constraintLabelMarkers)) {
				$constraintLabelMarkers = array();
			}
			foreach ($fieldData['constraints'] as $constraintData) {
				$constraints[] = $this->buildConstraint($constraintData);
				if (isset($constraintLabelMarkers[$constraintData['name']])) {
					if (is_array($constraintLabelMarkers[$constraintData['name']])) {
						$labelMarkers = array_merge($labelMarkers, $constraintLabelMarkers[$constraintData['name']]);
					} else {
						$labelMarkers[] = $constraintLabelMarkers[$constraintData['name']];
					}
				}
			}
		}
		if (isset($fieldData['label-markers'])) {
			if (is_array($fieldData['label-markers'])) {
				$labelMarkers = array_merge($labelMarkers, $fieldData['label-markers']);
			} else {
				$labelMarkers[] = $fieldData['label-markers'];
			}
		}
		$settings = isset($fieldData['settings']) ? $fieldData['settings'] : array();
		return $fieldTypeClass->newInstance($name, $this->getFieldValue($name, $defaultValue), $labelText, $labelMarkers, $constraints, $this->getFieldErrors($name), $settings);
	}

	/**
	 * Create a single constraint on a field.
	 *
	 * @param array $constraintData
	 *
	 * @return \Symfony\Component\Validator\Constraint
	 */
	public function buildConstraint(array $constraintData) {
		LoggerRegistry::debug('FormBuilder::buildConstraint()');
		$constraintClass = new \ReflectionClass(
			isset($constraintData['class']) ?
					$constraintData['class'] :
					sprintf(self::CLASS_NAME_FORMAT_CONSTRAINT, NameUtilities::convertToStudlyCaps($constraintData['name']))
		);
		return $constraintClass->newInstance(isset($constraintData['options']) ? $constraintData['options'] : null);
	}

	/**
	 * Create a single step of the form.
	 *
	 * @param FormInterface $form
	 * @param array $formData
	 * @param integer $stepIndex
	 *
	 * @return \Sitegear\Base\Form\StepInterface
	 */
	public function buildStep(FormInterface $form, array $formData, $stepIndex) {
		LoggerRegistry::debug('FormBuilder::buildStep()');
		$stepData = $formData['steps'][$stepIndex];
		$oneWay = isset($stepData['one-way']) ? $stepData['one-way'] : false;
		$heading = isset($stepData['heading']) ? $stepData['heading'] : null;
		$errorHeading = isset($stepData['error-heading']) ? $stepData['error-heading'] : null;
		$step = new Step($form, $stepIndex, $oneWay, $heading, $errorHeading);
		if (isset($stepData['fieldsets'])) {
			foreach ($stepData['fieldsets'] as $fieldsetData) {
				$step->addFieldset($this->buildFieldset($step, $fieldsetData));
			}
		}
		if (isset($stepData['processors'])) {
			foreach ($stepData['processors'] as $processorData) {
				$step->addProcessor($this->buildProcessor($processorData));
			}
		}
		return $step;
	}

	/**
	 * Create a single fieldset, which exists within a given step.
	 *
	 * @param \Sitegear\Base\Form\StepInterface $step
	 * @param array $fieldsetData
	 *
	 * @return \Sitegear\Base\Form\Fieldset
	 */
	public function buildFieldset(StepInterface $step, array $fieldsetData) {
		LoggerRegistry::debug('FormBuilder::buildFieldset()');
		$heading = isset($fieldsetData['heading']) ? $fieldsetData['heading'] : null;
		$fieldset = new Fieldset($step, $heading);
		foreach ($fieldsetData['fields'] as $fieldData) {
			if (!is_array($fieldData)) {
				$fieldData = array( 'field' => $fieldData );
			}
			$fieldset->addFieldReference(new FieldReference(
				$fieldData['field'],
				isset($fieldData['read-only']) && $fieldData['read-only'],
				!isset($fieldData['wrapped']) || $fieldData['wrapped']
			));
		}
		return $fieldset;
	}

	/**
	 * Create a single processor for a step of the form.
	 *
	 * @param array $processorData
	 *
	 * @return \Sitegear\Base\Form\Processor\FormProcessorInterface
	 */
	public function buildProcessor(array $processorData) {
		LoggerRegistry::debug('FormBuilder::buildProcessor()');
		$processor = new ModuleProcessor(
			$this->getFormsModule()->getEngine()->getModule($processorData['module']),
			$processorData['method'],
			isset($processorData['arguments']) ? $processorData['arguments'] : array(),
			isset($processorData['exception-field-names']) ? $processorData['exception-field-names'] : null,
			isset($processorData['exception-action']) ? $processorData['exception-action'] : null
		);
		if (isset($processorData['conditions'])) {
			foreach ($processorData['conditions'] as $conditionData) {
				$processor->addCondition($this->buildCondition($conditionData));
			}
		}
		return $processor;
	}

	/**
	 * Create a single condition for a single processor.
	 *
	 * @param array $conditionData
	 *
	 * @return ConditionInterface
	 */
	public function buildCondition(array $conditionData) {
		$conditionClass = new \ReflectionClass(
			sprintf(
				self::CLASS_NAME_FORMAT_CONDITION,
				NameUtilities::convertToStudlyCaps($conditionData['condition'])
			)
		);
		return $conditionClass->newInstance($conditionData['field'], $conditionData['values']);
	}

}
