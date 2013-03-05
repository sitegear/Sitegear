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
 * Defines the behaviour of a single "step" of a web form.  Each step consists of a field structure and any number of
 * processors.  The field structure must correspond to that expected by the relevant renderer, and is made up of
 * references by name to the fields in the parent form.
 */
interface StepInterface {

	/**
	 * @return \Sitegear\Base\Form\FormInterface Link to the parent form.
	 */
	public function getForm();

	/**
	 * @return integer The index of this step within the form.
	 */
	public function getStepIndex();

	/**
	 * @return boolean
	 */
	public function isOneWay();

	/**
	 * @param boolean $oneWay
	 *
	 * @return self
	 */
	public function setOneWay($oneWay);

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
	 * Retrieve the fieldsets within this step.
	 *
	 * @return FieldsetInterface[]
	 */
	public function getFieldsets();

	/**
	 * Add a fieldset to this step.
	 *
	 * @param FieldsetInterface $fieldset
	 *
	 * @return self
	 */
	public function addFieldset(FieldsetInterface $fieldset);

	/**
	 * Remove a fieldset from this step.
	 *
	 * @param FieldsetInterface $fieldset
	 *
	 * @return self
	 */
	public function removeFieldset(FieldsetInterface $fieldset);

	/**
	 * Remove all fieldsets from this step.
	 *
	 * @return self
	 */
	public function clearFieldsets();

	/**
	 * Retrieve all the fields referenced by this step of this form.  Read-only field references should be excluded.
	 *
	 * @return \Sitegear\Base\Form\Field\FieldInterface[]
	 */
	public function getReferencedFields();

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
