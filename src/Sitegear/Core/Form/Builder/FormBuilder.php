<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Form\Builder;

use Sitegear\Base\Form\Form;
use Sitegear\Base\Form\Step;
use Sitegear\Base\Form\StepInterface;
use Sitegear\Base\Form\FormInterface;
use Sitegear\Base\Form\FieldReference;
use Sitegear\Base\Form\Builder\FormBuilderInterface;
use Sitegear\Base\Form\Element\ButtonsElement;
use Sitegear\Base\Form\Element\FieldElement;
use Sitegear\Base\Form\Element\LabelElement;
use Sitegear\Base\Form\Element\FieldContainerElement;
use Sitegear\Base\Form\Element\FieldsetElement;
use Sitegear\Base\Form\Element\FormElement;
use Sitegear\Base\Form\Processor\ModuleProcessor;
use Sitegear\Base\Engine\EngineInterface;
use Sitegear\Util\ArrayUtilities;
use Sitegear\Util\NameUtilities;
use Sitegear\Util\UrlUtilities;
use Sitegear\Util\LoggerRegistry;

/**
 * Core FormBuilderInterface implementation.  Maps the format defined by the `FormsModule` data files into the Form
 * objects.
 */
class FormBuilder implements FormBuilderInterface {

	//-- Constants --------------------

	/**
	 * Default class name for FieldInterface implementations.
	 */
	const CLASS_NAME_FORMAT_FIELD = '\\Sitegear\\Base\\Form\\Field\\%sField';

	/**
	 * Default class name for Constraint implementations.
	 */
	const CLASS_NAME_FORMAT_CONSTRAINT = '\\Symfony\\Component\\Validator\\Constraints\\%s';

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Engine\EngineInterface
	 */
	private $engine;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\Engine\EngineInterface $engine
	 */
	public function __construct(EngineInterface $engine) {
		$this->engine = $engine;
	}

	//-- FormBuilderInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function buildForm($formData, callable $valueCallback, callable $errorsCallback, array $options) {
		LoggerRegistry::debug('FormBuilder::buildForm()');
		$submitUrl = UrlUtilities::generateLinkWithReturnUrl(
			isset($formData['submit-url']) ? $formData['submit-url'] : $options['submit-url'],
			$options['form-url'],
			'form-url'
		);
		$form = new Form(
			$submitUrl,
			isset($formData['target-url']) ? $formData['target-url'] : (isset($options['target-url']) ? $options['target-url'] : null),
			isset($formData['cancel-url']) ? $formData['cancel-url'] : (isset($options['cancel-url']) ? $options['cancel-url'] : null),
			isset($formData['submit-button']) ? $formData['submit-button'] : (isset($options['submit-button']) ? $options['submit-button'] : null),
			isset($formData['reset-button']) ? $formData['reset-button'] : (isset($options['reset-button']) ? $options['reset-button'] : null),
			isset($formData['back-button']) ? $formData['back-button'] : (isset($options['back-button']) ? $options['back-button'] : null)
		);
		$constraintLabelMarkers = isset($options['constraint-label-markers']) ? $options['constraint-label-markers'] : array();
		foreach ($formData['fields'] as $name => $fieldData) {
			$form->addField($this->buildField($name, $fieldData, $valueCallback($name), $constraintLabelMarkers, $errorsCallback($name)));
		}
		for ($i=0, $l=sizeof($formData['steps']); $i<$l; ++$i) {
			$form->addStep($this->buildStep($form, $formData, $i));
		}
		return $form;
	}

	//-- Internal Methods --------------------

	/**
	 * Create a single field.
	 *
	 * @param string $name
	 * @param array $fieldData
	 * @param mixed $value
	 * @param string[] $constraintLabelMarkers
	 * @param string[] $errors
	 *
	 * @return \Sitegear\Base\Form\Field\FieldInterface
	 */
	private function buildField($name, array $fieldData, $value=null, array $constraintLabelMarkers=null, array $errors=null) {
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
		// TODO Better method of determining settings
		$settings = isset($fieldData['settings']) ? $fieldData['settings'] : array();
		if (isset($fieldData['values'])) {
			$settings['values'] = $fieldData['values'];
		}
		return $fieldTypeClass->newInstance($name, $value ?: $defaultValue, $labelText, $labelMarkers, $constraints, $errors, $settings);
	}

	/**
	 * Create a single constraint on a field.
	 *
	 * @param array $constraintData
	 *
	 * @return \Symfony\Component\Validator\Constraint
	 */
	private function buildConstraint(array $constraintData) {
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
	private function buildStep(FormInterface $form, array $formData, $stepIndex) {
		LoggerRegistry::debug('FormBuilder::buildStep()');
		$stepData = $formData['steps'][$stepIndex];
		$processors = array();
		if (isset($stepData['processors'])) {
			foreach ($stepData['processors'] as $processorData) {
				$processors[] = $this->buildProcessor($processorData);
			}
		}
		$heading = isset($stepData['heading']) ? $stepData['heading'] : null;
		$errorHeading = isset($stepData['error-heading']) ? $stepData['error-heading'] : null;
		$step = new Step($form, $stepIndex, $heading, $errorHeading, $processors);
		$step->setRootElement($this->buildFormElement($step, $formData, $stepData));
		return $step;
	}

	/**
	 * Create a single processor for a step of the form.
	 *
	 * @param array $processorData
	 *
	 * @return \Sitegear\Base\Config\Processor\ProcessorInterface
	 */
	private function buildProcessor(array $processorData) {
		LoggerRegistry::debug('FormBuilder::buildProcessor()');
		return new ModuleProcessor(
			$processorData['arguments'],
			$this->engine->getModule($processorData['module']),
			$processorData['method']
		);
	}

	/**
	 * Create a `FormElement` instance.
	 *
	 * @param \Sitegear\Base\Form\StepInterface $step
	 * @param array $formData
	 * @param array $stepData
	 *
	 * @return \Sitegear\Base\Form\Element\FormElement
	 */
	private function buildFormElement(StepInterface $step, array $formData, array $stepData) {
		LoggerRegistry::debug('FormBuilder::buildFormElement()');
		$formElement = new FormElement($step, isset($formData['attributes']) ? $formData['attributes'] : array());
		foreach ($stepData['fieldsets'] as $fieldsetData) {
			$formElement->addChild($this->buildFieldsetElement($step, $fieldsetData));
		}
		$formElement->addChild(new ButtonsElement($step));
		return $formElement;
	}

	/**
	 * Create a `FieldsetElement` instance.
	 *
	 * @param \Sitegear\Base\Form\StepInterface $step
	 * @param array $fieldsetData
	 *
	 * @return \Sitegear\Base\Form\Element\FieldsetElement
	 */
	private function buildFieldsetElement(StepInterface $step, array $fieldsetData) {
		LoggerRegistry::debug('FormBuilder::buildFieldsetElement()');
		$fieldsetElement = new FieldsetElement($step, isset($formData['attributes']) ? $formData['attributes'] : array());
		foreach ($fieldsetData['fields'] as $fieldReferenceData) {
			$fieldsetElement->addChild($this->buildFieldContainerElement($step, $fieldReferenceData));
		}
		return $fieldsetElement;
	}

	/**
	 * Create a `FieldContainerElement` instance.
	 *
	 * @param \Sitegear\Base\Form\StepInterface $step
	 * @param $fieldReferenceData
	 *
	 * @return \Sitegear\Base\Form\Element\FieldContainerElement
	 */
	private function buildFieldContainerElement(StepInterface $step, $fieldReferenceData) {
		LoggerRegistry::debug('FormBuilder::buildFieldContainerElement()');
		if (!is_array($fieldReferenceData)) {
			$fieldReferenceData = array( 'field' => $fieldReferenceData );
		}
		$fieldReferenceData = ArrayUtilities::combine(array( 'element' => 'div', 'attributes' => array(), 'read-only' => false ), $fieldReferenceData);
		$fieldContainerElement = new FieldContainerElement($step, $fieldReferenceData['element'], $fieldReferenceData['attributes']);
		$fieldReference = new FieldReference($fieldReferenceData['field'], $fieldReferenceData['read-only']);
		return $fieldContainerElement
				->addChild(new LabelElement($step, $fieldReference))
				->addChild(new FieldElement($step, $fieldReference));
	}

}
