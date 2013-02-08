<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Engine\KernelEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides an abstract implementation of EventSubscriberInterface that provides a reference to an EngineInterface
 * instance.
 */
abstract class AbstractEngineKernelListener implements EventSubscriberInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Engine\EngineInterface
	 */
	private $engine;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\Engine\EngineInterface $engine
	 */
	public function __construct($engine) {
		$this->engine = $engine;
	}

	//-- Public Methods --------------------

	/**
	 * @return \Sitegear\Base\Engine\EngineInterface
	 */
	public function getEngine() {
		return $this->engine;
	}

}
