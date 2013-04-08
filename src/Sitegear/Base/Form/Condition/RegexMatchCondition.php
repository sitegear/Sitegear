<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Condition;

/**
 * Implements a condition that requires a field to match one of a list of regular expressions.
 *
 * Required options: 'field' (string), 'patterns' (array of regex).
 */
class RegexMatchCondition extends AbstractCondition {

	//-- ConditionInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function matches(array $values) {
		$result = false;
		$field = $this->getOption('field');
		if (isset($values[$field])) {
			$value = $values[$field];
			foreach ($this->getOption('patterns') as $pattern) {
				$result = $result || preg_match($pattern, $value);
			}
		}
		return $result;
	}

}
