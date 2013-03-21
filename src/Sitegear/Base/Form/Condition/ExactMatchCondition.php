<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Condition;

use Sitegear\Base\Form\Condition\ConditionInterface;

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
	private $matchValues;

	//-- Constructor --------------------

	/**
	 * @param string $field Name of the field to check.
	 * @param array $matchValues Values, one of which must match exactly to the field's submitted value.
	 */
	public function __construct($field, array $matchValues) {
		$this->field = $field;
		$this->matchValues = $matchValues;
	}

	//-- ConditionInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function matches(array $values) {
		$result = false;
		if (isset($values[$this->field])) {
			$value = $values[$this->field];
			foreach ($this->matchValues as $matchValue) {
				$result = $result || ($matchValue === $value);
			}
		}
		return $result;
	}

}
