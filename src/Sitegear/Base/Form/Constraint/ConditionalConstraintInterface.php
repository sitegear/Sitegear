<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Defines the behaviour of an object responsible for conditionally applying a Symfony Validator component Constraint
 * object.
 */
interface ConditionalConstraintInterface {

	/**
	 * Retrieve the Constraint object associated with this constraint processor.
	 *
	 * @return Constraint
	 */
	public function getConstraint();

	/**
	 * Determine whether the constraint should be applied, with the given data.
	 *
	 * @param array $values
	 *
	 * @return boolean
	 */
	public function shouldApplyConstraint(array $values);

}
