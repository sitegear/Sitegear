<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Decorator\Registry;

use Sitegear\View\Decorator\DecoratorInterface;

/**
 * Defines the behaviour of a registry for DecoratorInterface implementations, using a key associated with each
 * registered decorator that can later be used to retrieve it.
 */
interface DecoratorRegistryInterface {

	/**
	 * Add a decorator to the registry.
	 *
	 * @param string $key Key by which the decorator is activated.
	 * @param null|string|\Sitegear\View\Decorator\DecoratorInterface $decorator Null (or omitted) to get the
	 *   value, or a DecoratorInterface implementation or class name to instantiate, to set the value.
	 *
	 * @throw \LogicException If the decorator (key) is already registered.
	 */
	public function register($key, $decorator);

	/**
	 * Register the given map, which is equivalent to passing the keys and decorator values into $this->register().
	 *
	 * @param DecoratorInterface[] $decorators
	 *
	 * @throw \LogicException If the decorator (key) is already registered.
	 */
	public function registerMap(array $decorators);

	/**
	 * Remove a decorator from the registry.
	 *
	 * @param string $key Decorator key to remove.
	 *
	 * @throw \LogicException If the decorator (key) is not registered.
	 */
	public function deregister($key);

	/**
	 * Determine whether the given key is registered.
	 *
	 * @param string $key Decorator key to check.
	 *
	 * @return boolean Whether or not the key exists in this registry.
	 */
	public function isRegistered($key);

	/**
	 * Get the given key, if it is registered.
	 *
	 * @param string $key Decorator key to retrieve.
	 *
	 * @return \Sitegear\View\Decorator\DecoratorInterface Decorator with the given key, or null if it does not exist.
	 */
	public function getDecorator($key);

}
