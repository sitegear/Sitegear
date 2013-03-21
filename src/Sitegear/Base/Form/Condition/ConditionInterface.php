<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Condition;

/**
 * Describes the behaviour of a class responsible for checking a particular condition based on the values submitted
 * in the form.   This can be used for conditionally executing processors, for conditionally displaying fields, and for
 * conditionally enforcing constraints on a field.
 */
interface ConditionInterface {

	/**
	 * @param string $key
	 * @param mixed|null $default
	 *
	 * @return mixed|null
	 */
	public function getOption($key, $default=null);

	/**
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return self
	 */
	public function setOption($key, $value);

	/**
	 * Test the condition against the given array of form values.
	 *
	 * @param array $values
	 *
	 * @return boolean
	 */
	public function matches(array $values);

}
