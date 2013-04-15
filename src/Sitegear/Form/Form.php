<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Form;

use Sitegear\Form\Field\FieldInterface;

/**
 * Default implementation of FormInterface.
 */
class Form implements FormInterface {

	//-- Attributes --------------------

	/**
	 * @var string
	 */
	private $submitUrl;

	/**
	 * @var string|null
	 */
	private $targetUrl;

	/**
	 * @var string|null
	 */
	private $cancelUrl;

	/**
	 * @var string
	 */
	private $method;

	/**
	 * @var string[]|null
	 */
	private $submitButtonAttributes;

	/**
	 * @var string[]|null
	 */
	private $resetButtonAttributes;

	/**
	 * @var string[]|null
	 */
	private $backButtonAttributes;

	/**
	 * @var FieldInterface[]
	 */
	private $fields;

	/**
	 * @var array
	 */
	private $values;

	/**
	 * @var string[][]
	 */
	private $errors;

	/**
	 * @var StepInterface[]
	 */
	private $steps;

	//-- Constructor --------------------

	/**
	 * @param string $submitUrl
	 * @param string|null $targetUrl
	 * @param string|null $cancelUrl
	 * @param string|null $method
	 * @param array|null $values
	 * @param array[]|null $errors
	 * @param string[]|null $submitButtonAttributes
	 * @param string[]|null $resetButtonAttributes
	 * @param string[]|null $backButtonAttributes
	 */
	public function __construct($submitUrl, $targetUrl=null, $cancelUrl=null, $method=null, array $values=null, array $errors=null, $submitButtonAttributes=null, $resetButtonAttributes=null, $backButtonAttributes=null) {
		$this->submitUrl = $submitUrl;
		$this->targetUrl = $targetUrl;
		$this->cancelUrl = $cancelUrl;
		$this->setMethod($method ?: 'POST');
		$this->submitButtonAttributes = (is_null($submitButtonAttributes) || is_array($submitButtonAttributes)) ? $submitButtonAttributes : array( 'value' => $submitButtonAttributes );
		$this->resetButtonAttributes = (is_null($resetButtonAttributes) || is_array($resetButtonAttributes)) ? $resetButtonAttributes : array( 'value' => $resetButtonAttributes );
		$this->backButtonAttributes = (is_null($backButtonAttributes) || is_array($backButtonAttributes)) ? $backButtonAttributes : array( 'value' => $backButtonAttributes );
		$this->fields = array();
		$this->values = $values ?: array();
		$this->errors = $errors ?: array();
		$this->steps = array();
	}

	//-- FormInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getSubmitUrl() {
		return $this->submitUrl;
	}

	/**
	 * @inheritdoc
	 */
	public function setSubmitUrl($submitUrl) {
		$this->submitUrl = $submitUrl;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getTargetUrl() {
		return $this->targetUrl;
	}

	/**
	 * @inheritdoc
	 */
	public function setTargetUrl($targetUrl) {
		$this->targetUrl = $targetUrl;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getCancelUrl() {
		return $this->cancelUrl;
	}

	/**
	 * @inheritdoc
	 */
	public function setCancelUrl($cancelUrl) {
		$this->cancelUrl = $cancelUrl;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * @inheritdoc
	 */
	public function setMethod($method, $force=false) {
		$method = strtoupper($method);
		if (!in_array($method, array( 'GET', 'POST', 'PUT', 'DELETE' )) && !$force) {
			throw new \InvalidArgumentException(sprintf('Form: Cannot set invalid method "%s"', $method));
		}
		$this->method = $method;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getSubmitButtonAttributes() {
		return $this->submitButtonAttributes;
	}

	/**
	 * @inheritdoc
	 */
	public function setSubmitButtonAttributes(array $submitButtonAttributes) {
		$this->submitButtonAttributes = $submitButtonAttributes;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getResetButtonAttributes() {
		return $this->resetButtonAttributes;
	}

	/**
	 * @inheritdoc
	 */
	public function setResetButtonAttributes(array $resetButtonAttributes) {
		$this->resetButtonAttributes = $resetButtonAttributes;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getBackButtonAttributes() {
		return $this->backButtonAttributes;
	}

	/**
	 * @inheritdoc
	 */
	public function setBackButtonAttributes(array $backButtonAttributes) {
		$this->backButtonAttributes = $backButtonAttributes;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getField($name) {
		return isset($this->fields[$name]) ? $this->fields[$name] : null;
	}

	/**
	 * @inheritdoc
	 */
	public function addField(FieldInterface $field) {
		$this->fields[$field->getName()] = $field;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function removeField($field) {
		$fieldName = is_string($field) ? $field : $field->getName();
		unset($this->fields[$fieldName]);
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getValues() {
		return $this->values;
	}

	/**
	 * @inheritdoc
	 */
	public function getFieldValue($fieldName, $default=null) {
		return isset($this->values[$fieldName]) ? $this->values[$fieldName] : $default;
	}

	/**
	 * @inheritdoc
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * @inheritdoc
	 */
	public function getFieldErrors($fieldName) {
		return isset($this->errors[$fieldName]) ? $this->errors[$fieldName] : array();
	}

	/**
	 * @inheritdoc
	 */
	public function getStepsCount() {
		return sizeof($this->steps);
	}

	/**
	 * @inheritdoc
	 */
	public function getStep($index) {
		return $this->steps[$index];
	}

	/**
	 * @inheritdoc
	 */
	public function addStep(StepInterface $step, $index=null) {
		if (is_null($index)) {
			$this->steps[] = $step;
		} else {
			$this->steps = array_splice($this->steps, intval($index), 0, $step);
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function removeStep($step) {
		$index = is_int($step) ? $step : array_search($step, $this->steps);
		$this->steps = array_splice($this->steps, $index, 1);
		return $this;
	}

}
