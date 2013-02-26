<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Field;

use Symfony\Component\Validator\Constraint;
use Sitegear\Util\LoggerRegistry;

/**
 * Abstract base implementation of FieldInterface.
 */
abstract class AbstractField implements FieldInterface {

	//-- Attributes --------------------

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var mixed
	 */
	private $value;

	/**
	 * @var string
	 */
	private $labelText;

	/**
	 * @var string[]
	 */
	private $labelMarkers;

	/**
	 * @var Constraint[]
	 */
	private $constraints;

	/**
	 * @var string[]
	 */
	private $errors;

	/**
	 * @var array
	 */
	private $settings;

	//-- Constructor --------------------

	/**
	 * @param string $name
	 * @param mixed $value
	 * @param string|null $labelText
	 * @param string[]|null $labelMarkers
	 * @param Constraint[]|null $constraints
	 * @param string[]|null $errors
	 * @param array $settings
	 */
	public function __construct($name, $value=null, $labelText=null, array $labelMarkers=null, array $constraints=null, array $errors=null, array $settings=null) {
		$this->name = $name;
		$this->value = $value;
		$this->labelText = $labelText;
		$this->labelMarkers = $labelMarkers ?: array();
		$this->constraints = $constraints ?: array();
		$this->errors = $errors ?: array();
		$this->settings = $settings ?: array();
	}

	//-- FieldInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setValue($value) {
		$this->value = $value;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLabelText() {
		return $this->labelText;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setLabelText($labelText) {
		$this->labelText = $labelText;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLabelMarkers($glue=null) {
		return implode($glue ?: '', $this->labelMarkers);
	}

	/**
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	public function removeLabelMarker($labelMarker) {
		$index = is_integer($labelMarker) ? $labelMarker : array_search($labelMarker, $this->labelMarkers);
		$this->labelMarkers = array_splice($this->labelMarkers, $index, 1);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConstraints() {
		return $this->constraints;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addConstraint(Constraint $constraint, $index=null) {
		if (is_null($index)) {
			$this->constraints[] = $constraint;
		} else {
			$this->constraints = array_splice($this->constraints, intval($index), 0, $constraint);
		}
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeConstraint($constraint) {
		$index = is_integer($constraint) ? $constraint : array_search($constraint, $this->constraints);
		$this->constraints = array_splice($this->constraints, $index, 1);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setErrors(array $errors) {
		$this->errors = $errors;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSetting($key, $default=null) {
		return isset($this->settings[$key]) ? $this->settings[$key] : $default;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setSetting($key, $value) {
		$this->settings[$key] = $value;
		return $this;
	}

}
