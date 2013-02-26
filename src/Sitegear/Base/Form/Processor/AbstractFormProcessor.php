<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Processor;

/**
 * Partial abstract implementation of FormProcessorInterface.
 */
abstract class AbstractFormProcessor implements FormProcessorInterface {

	//-- Attributes --------------------

	/**
	 * @var array
	 */
	private $argumentDefaults;

	//-- Constructor --------------------

	public function __construct(array $argumentDefaults) {
		$this->argumentDefaults = $argumentDefaults ?: array();
	}

	//-- FormProcessorInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getArgumentDefaults() {
		return $this->argumentDefaults;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setArgumentDefaults(array $argumentDefaults) {
		$this->argumentDefaults = $argumentDefaults;
	}

}
