<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Constraint;

use Sitegear\Base\Form\Condition\ConditionInterface;

use Symfony\Component\Validator\Constraint;

/**
 * Default implementation of ConditionalConstraintInterface.  Utilises the Form\Condition package.
 */
class ConditionalConstraint implements ConditionalConstraintInterface {

	//-- Attributes --------------------

	/**
	 * @var Constraint
	 */
	private $constraint;

	/**
	 * @var ConditionInterface[]
	 */
	private $conditions;

	//-- Constructor --------------------

	/**
	 * @param Constraint $constraint
	 * @param ConditionInterface[] $conditions
	 */
	public function __construct(Constraint $constraint, array $conditions=null) {
		$this->constraint = $constraint;
		$this->conditions = $conditions ?: array();
	}

	//-- ConditionalConstraintInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getConstraint() {
		return $this->constraint;
	}

	/**
	 * @inheritdoc
	 */
	public function shouldApplyConstraint(array $values) {
		$result = true;
		foreach ($this->conditions as $condition) {
			$result = $result && $condition->matches($values);
		}
		return $result;
	}
}
