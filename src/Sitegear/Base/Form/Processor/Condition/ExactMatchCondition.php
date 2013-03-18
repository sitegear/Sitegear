<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Processor\Condition;

use Sitegear\Base\Form\Processor\Condition\ConditionInterface;

/**
 * Implements a condition that requires a field to have a value exactly matching one of a given list of values.  That
 * is: 'execute this processor only if field X has one of the values "foo", "bar" or "baz"'.
 */
class ExactMatchCondition implements ConditionInterface {

	//-- Attributes --------------------

	/**
	 * @var string
	 */
	private $field;

	/**
	 * @var array
	 */
	private $values;

	//-- Constructor --------------------

	/**
	 * @param string $field Name of the field to check.
	 * @param array $values Values, one of which must match exactly to the field's submitted value.
	 */
	public function __construct($field, array $values) {
		$this->field = $field;
		$this->values = $values;
	}

	//-- ConditionInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function matches(array $values) {
		$result = false;
		if (isset($values[$this->field])) {
			$target = $values[$this->field];
			foreach ($this->values as $value) {
				$result = $result || ($value === $target);
			}
		}
		return $result;
	}

}
