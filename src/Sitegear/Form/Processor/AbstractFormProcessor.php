<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Form\Processor;

use Sitegear\Form\Condition\ConditionInterface;

/**
 * Partial abstract implementation of FormProcessorInterface.
 */
abstract class AbstractFormProcessor implements FormProcessorInterface {

	//-- Constants --------------------

	/**
	 * Action to use on exceptions by default if nothing specified.
	 */
	const DEFAULT_EXCEPTION_ACTION = FormProcessorInterface::EXCEPTION_ACTION_MESSAGE;

	//-- Attributes --------------------

	/**
	 * @var array
	 */
	private $argumentDefaults;

	/**
	 * @var ConditionInterface[]
	 */
	private $conditions;

	/**
	 * @var string
	 */
	private $exceptionAction;

	//-- Constructor --------------------

	/**
	 * @param array $argumentDefaults
	 * @param string|null $exceptionAction EXCEPTION_ACTION_RETHROW by default
	 */
	public function __construct(array $argumentDefaults=null, $exceptionAction=null) {
		$this->argumentDefaults = $argumentDefaults ?: array();
		$this->conditions = array();
		$this->exceptionAction = $exceptionAction ?: self::DEFAULT_EXCEPTION_ACTION;
	}

	//-- FormProcessorInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getArgumentDefaults() {
		return $this->argumentDefaults;
	}

	/**
	 * @inheritdoc
	 */
	public function setArgumentDefaults(array $argumentDefaults) {
		$this->argumentDefaults = $argumentDefaults;
	}

	/**
	 * @inheritdoc
	 */
	public function getConditions() {
		return $this->conditions;
	}

	/**
	 * @inheritdoc
	 */
	public function addCondition(ConditionInterface $condition) {
		$this->conditions[] = $condition;
	}

	/**
	 * @inheritdoc
	 */
	public function clearConditions() {
		$this->conditions = array();
	}

	/**
	 * @inheritdoc
	 */
	public function shouldExecute(array $values) {
		$result = true;
		foreach ($this->getConditions() as $condition) {
			$result = $result && $condition->matches($values);
		}
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function getExceptionAction() {
		return $this->exceptionAction;
	}

	/**
	 * @inheritdoc
	 */
	public function setExceptionAction($exceptionAction) {
		$this->exceptionAction = $exceptionAction;
		return $this;
	}

}
