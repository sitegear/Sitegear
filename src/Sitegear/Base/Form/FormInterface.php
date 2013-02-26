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
 * Defines the behaviour of a web form.  This is the top-level behaviour.  A form consists of fields and steps, which
 * are encapsulated by the FieldInterface and StepInterface respectively.  The form directly contains all of the fields
 * used; these are then referenced by name from the various steps.  In this way, the same field can be reused within a
 * multi-step form, e.g. once as an input and once as a read-only reminder of the value entered.
 */
interface FormInterface {

	/**
	 * @return string
	 */
	public function getSubmitUrl();

	/**
	 * @param string $submitUrl
	 *
	 * @return self
	 */
	public function setSubmitUrl($submitUrl);

	/**
	 * @return string|null
	 */
	public function getTargetUrl();

	/**
	 * @param string|null $targetUrl
	 *
	 * @return self
	 */
	public function setTargetUrl($targetUrl);

	/**
	 * @return string|null
	 */
	public function getCancelUrl();

	/**
	 * @param string|null $cancelUrl
	 *
	 * @return self
	 */
	public function setCancelUrl($cancelUrl);

	/**
	 * @return string
	 */
	public function getSubmitButtonAttributes();

	/**
	 * @param string[] $submitButtonAttributes
	 *
	 * @return self
	 */
	public function setSubmitButtonAttributes(array $submitButtonAttributes);

	/**
	 * @return string
	 */
	public function getResetButtonAttributes();

	/**
	 * @param string[] $resetButtonAttributes
	 *
	 * @return self
	 */
	public function setResetButtonAttributes(array $resetButtonAttributes);

	/**
	 * @param string $name
	 *
	 * @return FieldInterface
	 */
	public function getField($name);

	/**
	 * @param FieldInterface $field
	 *
	 * @return self
	 */
	public function addField(FieldInterface $field);

	/**
	 * @param string|FieldInterface $field
	 *
	 * @return self
	 */
	public function removeField($field);

	/**
	 * @return integer
	 */
	public function getStepsCount();

	/**
	 * @param integer $index
	 *
	 * @return StepInterface
	 */
	public function getStep($index);

	/**
	 * @param StepInterface $step
	 * @param integer|null $index
	 *
	 * @return self
	 */
	public function addStep($step, $index=null);

	/**
	 * @param integer|StepInterface $step
	 *
	 * @return self
	 */
	public function removeStep($step);

}
