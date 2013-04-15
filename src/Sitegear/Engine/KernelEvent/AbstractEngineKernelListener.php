<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Engine\KernelEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides an abstract implementation of EventSubscriberInterface that provides a reference to an EngineInterface
 * instance.
 */
abstract class AbstractEngineKernelListener implements EventSubscriberInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Engine\EngineInterface
	 */
	private $engine;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Engine\EngineInterface $engine
	 */
	public function __construct($engine) {
		$this->engine = $engine;
	}

	//-- Public Methods --------------------

	/**
	 * @return \Sitegear\Engine\EngineInterface
	 */
	public function getEngine() {
		return $this->engine;
	}

}
