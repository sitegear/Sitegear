<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Field;

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
