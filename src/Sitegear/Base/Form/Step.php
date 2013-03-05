<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form;

use Sitegear\Base\Form\Processor\FormProcessorInterface;

/**
 * Default implementation of StepInterface.
 */
class Step implements StepInterface {

	//-- Attributes --------------------

	/**
	 * @var FormInterface
	 */
	private $form;

	/**
	 * @var integer
	 */
	private $stepIndex;

	/**
	 * @var boolean
	 */
	private $oneWay;

	/**
	 * @var string
	 */
	private $heading;

	/**
	 * @var string
	 */
	private $errorHeading;

	/**
	 * @var FieldsetInterface[]
	 */
	private $fieldsets;

	/**
	 * @var FormProcessorInterface[]
	 */
	private $processors;

	//-- Constructor --------------------

	/**
	 * @param FormInterface $form
	 * @param integer $stepIndex
	 * @param boolean $oneWay
	 * @param string|null $heading
	 * @param string|null $errorHeading
	 */
	public function __construct(FormInterface $form, $stepIndex, $oneWay=false, $heading=null, $errorHeading=null) {
		$this->form = $form;
		$this->stepIndex = intval($stepIndex);
		$this->oneWay = $oneWay;
		$this->heading = $heading;
		$this->errorHeading = $errorHeading;
		$this->fieldsets = array();
		$this->processors = array();
	}

	//-- StepInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getForm() {
		return $this->form;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getStepIndex() {
		return $this->stepIndex;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isOneWay() {
		return $this->oneWay;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setOneWay($oneWay) {
		$this->oneWay = $oneWay;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHeading() {
		return $this->heading;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setHeading($heading) {
		$this->heading = $heading;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorHeading() {
		return $this->errorHeading;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setErrorHeading($errorHeading) {
		$this->errorHeading = $errorHeading;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFieldsets() {
		return $this->fieldsets;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addFieldset(FieldsetInterface $fieldset) {
		$this->fieldsets[] = $fieldset;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeFieldset(FieldsetInterface $fieldset) {
		$this->fieldsets = array_filter($this->fieldsets, function($f) use ($fieldset) {
			return $f !== $fieldset;
		});
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function clearFieldsets() {
		$this->fieldsets = array();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getReferencedFields() {
		$fields = array();
		foreach ($this->getFieldsets() as $fieldset) {
			foreach ($fieldset->getFieldReferences() as $fieldReference) {
				if (!$fieldReference->isReadOnly()) {
					$fields[] = $this->getForm()->getField($fieldReference->getFieldName());
				}
			}
		}
		return $fields;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getProcessors() {
		return $this->processors;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addProcessor(FormProcessorInterface $processor, $index=null) {
		if (is_null($index)) {
			$this->processors[] = $processor;
		} else {
			$this->processors = array_splice($this->processors, intval($index), 0, $processor);
		}
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeProcessor($processor) {
		$index = is_integer($processor) ? $processor : array_search($processor, $this->processors);
		$this->processors = array_splice($this->processors, $index, 1);
		return $this;
	}

}
