<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Processor;

/**
 * Defines the behaviour of a form processor, which can perform any arbitrary action upon successful (valid) submission
 * of a form step.
 */
interface FormProcessorInterface {

	//-- Constants --------------------

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
	 * @return callable
	 */
	public function getProcessorMethod();

	/**
	 * @return array
	 */
	public function getArgumentDefaults();

	/**
	 * @param array $argumentDefaults
	 *
	 * @return self
	 */
	public function setArgumentDefaults(array $argumentDefaults);

	/**
	 * @return string[]
	 */
	public function getExceptionFieldNames();

	/**
	 * @param string[] $exceptionFieldNames
	 *
	 * @return self
	 */
	public function setExceptionFieldNames(array $exceptionFieldNames);

	/**
	 * @return string One of the EXCEPTION_ACTION_* constants defined in FormProcessorInterface.
	 */
	public function getExceptionAction();

	/**
	 * @param string $exceptionAction One of the EXCEPTION_ACTION_* constants defined in FormProcessorInterface.
	 *
	 * @return self
	 */
	public function setExceptionAction($exceptionAction);

}
