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
 * Defines the behaviour of a single "step" of a web form.  Each step consists of a field structure and any number of
 * processors.  The field structure must correspond to that expected by the relevant renderer, and is made up of
 * references by name to the fields in the parent form.
 */
interface StepInterface {

	/**
	 * @return FormInterface Link to the parent form.
	 */
	public function getForm();

	/**
	 * @return string
	 */
	public function getHeading();

	/**
	 * @param string $heading
	 *
	 * @return self
	 */
	public function setHeading($heading);

	/**
	 * @return string
	 */
	public function getErrorHeading();

	/**
	 * @param string $errorHeading
	 *
	 * @return self
	 */
	public function setErrorHeading($errorHeading);

	/**
	 * @return ElementInterface
	 */
	public function getRootElement();

	/**
	 * @param ElementInterface $root
	 *
	 * @return self
	 */
	public function setRootElement(ElementInterface $root);

	/**
	 * @return FormProcessorInterface[]
	 */
	public function getProcessors();

	/**
	 * @param FormProcessorInterface $processor
	 * @param integer|null $index
	 *
	 * @return self
	 */
	public function addProcessor(FormProcessorInterface $processor, $index=null);

	/**
	 * @param int|FormProcessorInterface $processor
	 *
	 * @return self
	 */
	public function removeProcessor($processor);

}
