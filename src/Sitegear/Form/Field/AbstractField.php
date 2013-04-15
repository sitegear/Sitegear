<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Form\Field;

use Sitegear\Form\Condition\ConditionInterface;
use Sitegear\Form\Constraint\ConditionalConstraintInterface;
use Sitegear\Form\FormInterface;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\Validator\Constraint;

/**
 * Abstract base implementation of FieldInterface.
 */
abstract class AbstractField implements FieldInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Form\FormInterface
	 */
	private $form;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var mixed
	 */
	private $defaultValue;

	/**
	 * @var string
	 */
	private $labelText;

	/**
	 * @var string[]
	 */
	private $labelMarkers;

	/**
	 * @var ConditionalConstraintInterface[]
	 */
	private $conditionalConstraints;

	/**
	 * @var ConditionInterface[]
	 */
	private $includeConditions;

	/**
	 * @var array
	 */
	private $settings;

	//-- Constructor --------------------

	/**
	 * @param FormInterface $form
	 * @param string $name
	 * @param mixed $defaultValue
	 * @param string|null $labelText
	 * @param string[]|null $labelMarkers
	 * @param ConditionalConstraintInterface[]|null $conditionalConstraints
	 * @param ConditionInterface[]|null $includeConditions
	 * @param array $settings
	 */
	public function __construct(FormInterface $form, $name, $defaultValue=null, $labelText=null, array $labelMarkers=null, array $conditionalConstraints=null, array $includeConditions=null, array $settings=null) {
		$this->form = $form;
		$this->name = $name;
		$this->defaultValue = $defaultValue;
		$this->labelText = $labelText;
		$this->labelMarkers = $labelMarkers ?: array();
		$this->conditionalConstraints = $conditionalConstraints ?: array();
		$this->includeConditions = $includeConditions ?: array();
		$this->settings = $settings ?: array();
	}

	//-- FieldInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getForm() {
		return $this->form;
	}

	/**
	 * @inheritdoc
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @inheritdoc
	 */
	public function getDefaultValue() {
		return $this->defaultValue;
	}

	/**
	 * @inheritdoc
	 */
	public function setDefaultValue($defaultValue) {
		$this->defaultValue = $defaultValue;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getLabelText() {
		return $this->labelText;
	}

	/**
	 * @inheritdoc
	 */
	public function setLabelText($labelText) {
		$this->labelText = $labelText;
		return $this;
	}

	/**
	 * @inheritdoc
	 *
	 * This implementation uses a default separator of a single whitespace character.
	 */
	public function getLabelMarkers($separator=null) {
		$separator = $separator ?: ' ';
		return $separator . implode($separator, $this->labelMarkers);
	}

	/**
	 * @inheritdoc
	 */
	public function addLabelMarker($labelMarker, $index=null) {
		if (is_null($index)) {
			$this->labelMarkers[] = $labelMarker;
		} else {
			$this->labelMarkers = array_splice($this->labelMarkers, intval($index), 0, $labelMarker);
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function removeLabelMarker($labelMarker) {
		$index = is_integer($labelMarker) ? $labelMarker : array_search($labelMarker, $this->labelMarkers);
		$this->labelMarkers = array_splice($this->labelMarkers, $index, 1);
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getConditionalConstraints() {
		return $this->conditionalConstraints;
	}

	/**
	 * @inheritdoc
	 */
	public function addConditionalConstraint(ConditionalConstraintInterface $conditionalConstraint) {
		$this->conditionalConstraints[] = $conditionalConstraint;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function removeConditionalConstraint(ConditionalConstraintInterface $conditionalConstraint) {
		if (($index = array_search($conditionalConstraint, $this->conditionalConstraints)) !== false) {
			$this->conditionalConstraints = array_splice($this->conditionalConstraints, $index, 1);
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getIncludeConditions() {
		return $this->includeConditions;
	}

	/**
	 * @inheritdoc
	 */
	public function addIncludeCondition(ConditionInterface $includeCondition) {
		$this->includeConditions[] = $includeCondition;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function removeIncludeCondition(ConditionInterface $includeCondition) {
		if (($index = array_search($includeCondition, $this->includeConditions)) !== false) {
			$this->includeConditions = array_splice($this->includeConditions, $index, 1);
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function shouldBeIncluded(array $values) {
		$result = true;
		foreach ($this->getIncludeConditions() as $includeCondition) {
			$result = $result && $includeCondition->matches($values);
		}
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function getSetting($key, $default=null) {
		return isset($this->settings[$key]) ? $this->settings[$key] : $default;
	}

	/**
	 * @inheritdoc
	 */
	public function setSetting($key, $value) {
		$this->settings[$key] = $value;
		return $this;
	}

}
