<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Form;

/**
 * Defines the behaviour of a fieldset, which is an arbitrary collection of field references.  A step may contain one
 * or more fieldsets.
 */
interface FieldsetInterface {

	/**
	 * Retrieve the step containing this fieldset.
	 *
	 * @return \Sitegear\Form\StepInterface
	 */
	public function getStep();

	/**
	 * Retrieve the fieldset heading, normally placed in a <legend> element.
	 *
	 * @return string
	 */
	public function getHeading();

	/**
	 * Update the fieldset heading (legend).
	 *
	 * @param string $heading
	 *
	 * @return self
	 */
	public function setHeading($heading);

	/**
	 * Retrieve the list of field references in this fieldset.
	 *
	 * @return FieldReference[]
	 */
	public function getFieldReferences();

	/**
	 * Add one or more fields to this fieldset.
	 *
	 * @param FieldReference $fieldReference
	 *
	 * @return self
	 *
	 * @throws \InvalidArgumentException
	 */
	public function addFieldReference(FieldReference $fieldReference);

	/**
	 * Remove one or more fieldsets from this fieldset.
	 *
	 * @param FieldReference $fieldReference
	 *
	 * @return self
	 *
	 * @throws \InvalidArgumentException
	 */
	public function removeFieldReference(FieldReference $fieldReference);

	/**
	 * Remove all field references from this fieldset.
	 *
	 * @return self
	 */
	public function clearFieldReferences();

}
