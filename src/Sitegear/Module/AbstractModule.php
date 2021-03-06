<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module;

use Sitegear\Engine\EngineInterface;
use Sitegear\Util\NameUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;

/**
 * Abstract base Module interface implementation, provided as a convenience.
 */
abstract class AbstractModule implements ModuleInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Engine\EngineInterface The container of this module.
	 */
	private $engine;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Engine\EngineInterface $engine Container.
	 */
	public function __construct(EngineInterface $engine) {
		LoggerRegistry::debug('new AbstractModule()');
		$this->engine = $engine;
	}

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getEngine() {
		return $this->engine;
	}

	/**
	 * @inheritdoc
	 */
	public function getModuleRoot() {
		$obj = new \ReflectionClass($this);
		return dirname($obj->getFileName());
	}

	/**
	 * @inheritdoc
	 *
	 * Provides a default implementation which returns an empty array.
	 */
	public function getResourceMap() {
		return array();
	}

}
