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
	 * @return callable
	 */
	public function getCallable();

}
