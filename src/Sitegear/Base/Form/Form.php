<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form;

use Sitegear\Base\Form\Field\FieldInterface;

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
	 * @var StepInterface[]
	 */
	private $steps;

	//-- Constructor --------------------

	/**
	 * @param string $submitUrl
	 * @param string|null $targetUrl
	 * @param string|null $cancelUrl
	 * @param string[]|null $submitButtonAttributes
	 * @param string[]|null $resetButtonAttributes
	 * @param string[]|null $backButtonAttributes
	 */
	public function __construct($submitUrl, $targetUrl=null, $cancelUrl=null, $submitButtonAttributes=null, $resetButtonAttributes=null, $backButtonAttributes=null) {
		$this->submitUrl = $submitUrl;
		$this->targetUrl = $targetUrl;
		$this->cancelUrl = $cancelUrl;
		$this->submitButtonAttributes = is_array($submitButtonAttributes) ? $submitButtonAttributes : array( 'value' => $submitButtonAttributes );
		$this->resetButtonAttributes = is_array($resetButtonAttributes) || !is_string($resetButtonAttributes) ?
				$resetButtonAttributes : array( 'value' => $resetButtonAttributes );
		$this->backButtonAttributes = is_array($backButtonAttributes) || !is_string($backButtonAttributes) ?
				$backButtonAttributes : array( 'value' => $backButtonAttributes );
		$this->fields = array();
		$this->steps = array();
	}

	//-- FormInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getSubmitUrl() {
		return $this->submitUrl;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setSubmitUrl($submitUrl) {
		$this->submitUrl = $submitUrl;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTargetUrl() {
		return $this->targetUrl;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setTargetUrl($targetUrl) {
		$this->targetUrl = $targetUrl;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCancelUrl() {
		return $this->cancelUrl;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setCancelUrl($cancelUrl) {
		$this->cancelUrl = $cancelUrl;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSubmitButtonAttributes() {
		return $this->submitButtonAttributes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setSubmitButtonAttributes(array $submitButtonAttributes) {
		$this->submitButtonAttributes = $submitButtonAttributes;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getResetButtonAttributes() {
		return $this->resetButtonAttributes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setResetButtonAttributes(array $resetButtonAttributes) {
		$this->resetButtonAttributes = $resetButtonAttributes;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBackButtonAttributes() {
		return $this->backButtonAttributes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setBackButtonAttributes(array $backButtonAttributes) {
		$this->backButtonAttributes = $backButtonAttributes;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getField($name) {
		return isset($this->fields[$name]) ? $this->fields[$name] : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addField(FieldInterface $field) {
		$this->fields[$field->getName()] = $field;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeField($field) {
		$fieldName = is_string($field) ? $field : $field->getName();
		unset($this->fields[$fieldName]);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getStepsCount() {
		return sizeof($this->steps);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getStep($index) {
		return $this->steps[$index];
	}

	/**
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	public function removeStep($step) {
		$index = is_int($step) ? $step : array_search($step, $this->steps);
		$this->steps = array_splice($this->steps, $index, 1);
		return $this;
	}

}
