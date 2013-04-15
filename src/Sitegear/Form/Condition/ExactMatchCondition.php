<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Form\Condition;

/**
 * Implements a condition that requires a field to have a value exactly matching one of a given list of values.
 *
 * Required options: 'field' (string), 'values' (array of values to match against)
 */
class ExactMatchCondition extends AbstractCondition {

	//-- ConditionInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function matches(array $values) {
		$result = false;
		$field = $this->getOption('field');
		if (isset($values[$field])) {
			$value = $values[$field];
			foreach ($this->getOption('values') as $matchValue) {
				$result = $result || ($matchValue === $value);
			}
		}
		return $result;
	}

}
