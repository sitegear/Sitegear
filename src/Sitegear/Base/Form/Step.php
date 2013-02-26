<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form;

use Sitegear\Base\Form\Element\ElementInterface;
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
	 * @var ElementInterface
	 */
	private $root;

	/**
	 * @var string
	 */
	private $heading;

	/**
	 * @var string
	 */
	private $errorHeading;

	/**
	 * @var FormProcessorInterface[]
	 */
	private $processors;

	//-- Constructor --------------------

	/**
	 * @param FormInterface $form
	 * @param string|null $heading
	 * @param string|null $errorHeading
	 * @param array|null $processors
	 */
	public function __construct(FormInterface $form, $heading=null, $errorHeading=null, array $processors=null) {
		$this->form = $form;
		$this->heading = $heading;
		$this->errorHeading = $errorHeading;
		$this->processors = $processors ?: array();
		$this->root = null;
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
	 * @return string
	 */
	public function getErrorHeading() {
		return $this->errorHeading;
	}

	/**
	 * @param string $errorHeading
	 *
	 * @return self
	 */
	public function setErrorHeading($errorHeading) {
		$this->errorHeading = $errorHeading;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRootElement() {
		return $this->root;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setRootElement(ElementInterface $root) {
		$this->root = $root;
		return $this;
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
