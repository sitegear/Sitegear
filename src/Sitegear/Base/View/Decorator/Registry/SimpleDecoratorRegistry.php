<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\View\Decorator\Registry;

use Sitegear\Util\TypeUtilities;

/**
 * Default implementation of the DecoratorRegistryInterface.
 */
class SimpleDecoratorRegistry implements DecoratorRegistryInterface {

	//-- Attributes --------------------

	private $decorators;

	//-- Constructor --------------------

	public function __construct() {
		$this->decorators = array();
	}

	//-- DecoratorRegistryInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function register($key, $decorator) {
		if ($this->isRegistered($key)) {
			throw new \LogicException(sprintf('The given decorator key "%s" cannot be registered because it already exists.', $key));
		}
		$this->decorators[$key] = TypeUtilities::buildTypeCheckedObject(
			$decorator,
			'decorator',
			null,
			'\\Sitegear\\Base\\View\\Decorator\\DecoratorInterface'
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function registerMap(array $decorators) {
		foreach ($decorators as $key => $decorator) {
			$this->register($key, $decorator);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function deregister($key) {
		if (!$this->isRegistered($key)) {
			throw new \LogicException(sprintf('The given decorator key "%s" cannot be deregistered because it does not exist.', $key));
		}
		unset($this->decorators[$key]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isRegistered($key) {
		return array_key_exists($key, $this->decorators);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDecorator($key) {
		return isset($this->decorators[$key]) ? $this->decorators[$key] : null;
	}

}
