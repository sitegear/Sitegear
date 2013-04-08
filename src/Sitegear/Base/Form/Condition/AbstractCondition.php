<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Condition;

/**
 * Abstract partial implementation of ConditionInterface, implements storing and management of options, but not the
 * `matches()` method.
 */
abstract class AbstractCondition implements ConditionInterface {

	//-- Attributes --------------------

	/**
	 * @var array
	 */
	private $options;

	//-- Constructor --------------------

	/**
	 * @param array|null $options
	 */
	public function __construct(array $options=null) {
		$this->options = $options ?: array();
	}

	//-- ConditionInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getOption($key, $default=null) {
		return isset($this->options[$key]) ? $this->options[$key] : $default;
	}

	/**
	 * @inheritdoc
	 */
	public function setOption($key, $value) {
		$this->options[$key] = $value;
	}

}
