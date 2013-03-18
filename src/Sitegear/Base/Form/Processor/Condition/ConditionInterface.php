<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Processor\Condition;

/**
 * Describes the behaviour of a class responsible for checking a particular condition based on the values submitted
 * in the form.
 */
interface ConditionInterface {

	/**
	 * Test the condition against the given array of form values.
	 *
	 * @param array $values
	 *
	 * @return boolean
	 */
	public function matches(array $values);

}
