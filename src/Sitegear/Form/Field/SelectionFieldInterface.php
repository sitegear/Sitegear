<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Form\Field;

/**
 * Defines the behaviour of a field which restricts the user to selection between a set of available values (as opposed
 * to direct entry of a value).
 */
interface SelectionFieldInterface extends FieldInterface {

	/**
	 * @return bool
	 */
	public function isMultiple();

	/**
	 * @return string[]
	 */
	public function getValues();

	/**
	 * @param string $value
	 * @param string $label
	 *
	 * @return self
	 */
	public function addValue($value, $label);

	/**
	 * @param string $value
	 *
	 * @return self
	 */
	public function removeValue($value);

}
