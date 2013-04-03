<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Module;

use Sitegear\Base\Engine\EngineInterface;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Util\NameUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;

/**
 * Abstract base Module interface implementation, provided as a convenience.
 */
abstract class AbstractModule implements ModuleInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Engine\EngineInterface The container of this module.
	 */
	private $engine;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\Engine\EngineInterface $engine Container.
	 */
	public function __construct(EngineInterface $engine) {
		LoggerRegistry::debug(sprintf('Instantiating AbstractModule of actual class "%s"', get_class($this)));
		$this->engine = $engine;
	}

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function start() {
		// Default implementation does nothing; this method won't be used by most modules
	}

	/**
	 * {@inheritDoc}
	 */
	public function stop() {
		// Default implementation does nothing; this method won't be used by most modules
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEngine() {
		return $this->engine;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getModuleRoot() {
		$obj = new \ReflectionClass($this);
		return dirname($obj->getFileName());
	}

	/**
	 * {@inheritDoc}
	 *
	 * Provides a default implementation which returns an empty array.
	 */
	public function getResourceMap() {
		return array();
	}

	/**
	 * Apply the module's default view settings, which are present as a baseline for all views (components and pages).
	 *
	 * @param ViewInterface $view
	 */
	public function applyViewDefaults(ViewInterface $view) {
		// Do nothing by default
	}

}
