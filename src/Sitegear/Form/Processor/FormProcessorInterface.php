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
 * Defines the behaviour of a form processor, which can perform any arbitrary action upon successful (valid) submission
 * of a form step.
 */
interface FormProcessorInterface {

	//-- Constants --------------------

	/**
	 * Exception action constant signifying that if the processor method throws an exception, the message should be
	 * displayed to the user and the form submission should fail.
	 */
	const EXCEPTION_ACTION_MESSAGE = 'message';

	/**
	 * Exception action constant signifying that if the processor method throws an exception, it should be ignored,
	 * i.e. not rethrown and not cause the form submission to fail (this does not affect the action of exception field
	 * names).
	 */
	const EXCEPTION_ACTION_IGNORE = 'ignore';

	/**
	 * Exception action constant signifying that if the processor method throws an exception, it should cause the
	 * form submission to fail, but should not be rethrown.  This can be useful for "expected" exceptions that should
	 * not result in a dead-end for the user.
	 */
	const EXCEPTION_ACTION_FAIL = 'fail';

	/**
	 * Exception action constant signifying that any processor method exceptions should be rethrown.  This will result
	 * in a HTTP 500 error page for the user.
	 */
	const EXCEPTION_ACTION_RETHROW = 'rethrow';

	//-- Methods --------------------

	/**
	 * Get the method that should be called when executing this processor.
	 *
	 * @return callable
	 */
	public function getProcessorMethod();

	/**
	 * Get the default arguments for the method called to execute this processor.
	 *
	 * @return array
	 */
	public function getArgumentDefaults();

	/**
	 * Modify the default arguments for the method called to execute this processor.  Completely overrides any previous
	 * default values.
	 *
	 * @param array $argumentDefaults
	 *
	 * @return self
	 */
	public function setArgumentDefaults(array $argumentDefaults);

	/**
	 * @return ConditionInterface[]
	 */
	public function getConditions();

	/**
	 * Add a condition to this form processor.
	 *
	 * @param \Sitegear\Form\Condition\ConditionInterface $condition Condition which must be satisfied before the processor will be executed.
	 *
	 * @return self
	 */
	public function addCondition(ConditionInterface $condition);

	/**
	 * Remove all conditions from this form processor.
	 *
	 * @return self
	 */
	public function clearConditions();

	/**
	 * Determine whether the processor should be executed based on the given form values.
	 *
	 * @param array $values
	 *
	 * @return boolean
	 */
	public function shouldExecute(array $values);

	/**
	 * Retrieve the exception action, which indicates how exceptions occurring during execution of the processor should
	 * be handled.
	 *
	 * @return string One of the EXCEPTION_ACTION_* constants defined in FormProcessorInterface.
	 */
	public function getExceptionAction();

	/**
	 * Modify the exception action, which indicates how exceptions occurring during execution of the processor should
	 * be handled.
	 *
	 * @param string $exceptionAction One of the EXCEPTION_ACTION_* constants defined in FormProcessorInterface.
	 *
	 * @return self
	 */
	public function setExceptionAction($exceptionAction);

}
