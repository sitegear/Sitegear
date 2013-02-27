<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Element;

use Sitegear\Base\Form\StepInterface;

/**
 * Partial implementation of `ElementInterface`.  This implementation only implements the `getStep()` method and
 * requires a `StepInterface` implementation as constructor argument.
 */
abstract class AbstractElement implements ElementInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Form\StepInterface
	 */
	private $step;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\Form\StepInterface $step
	 */
	public function __construct(StepInterface $step) {
		$this->step = $step;
	}

	//-- ElementInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getStep() {
		return $this->step;
	}

}
