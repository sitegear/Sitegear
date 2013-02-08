<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Config;

/**
 * Defines the behaviour of an object that is configurable.
 */
interface ConfigurableInterface {

	/**
	 * Configure the object.  It is up to the implementation how this happens, i.e. whether the incoming configuration
	 * data completely replaces, or is merged with, the existing configuration data (if any).  This should also setup
	 * any configuration processors on the container, including any passed in via $additionalProcessors.
	 *
	 * @param null|array|string|\ArrayObject|\Sitegear\Base\Config\Container\ConfigContainerInterface $config
	 *   Configuration data, object or filename to configure this object with.  Null means to use only defaults.
	 * @param \Sitegear\Base\Config\ConfigLoader $loader Configuration loader.
	 * @param null|array $additionalProcessors Array of ProcessorInterface implementations.
	 *
	 * @return self Fluent pattern.
	 *
	 * @throws \LogicException If the method is called more than once illegally.  This may be allowed in some
	 *   implementations but does not have to be.
	 * @throws \InvalidArgumentException If the given argument is not accepted by the implementation.
	 */
	public function configure($config=null, ConfigLoader $loader=null, array $additionalProcessors=null);

	/**
	 * Retrieve the given configuration item.
	 *
	 * @param string $key Key to lookup.
	 * @param mixed $default Value to return if no value is found in the configuration.
	 *
	 * @return mixed|null Configuration value, or null if no such key exists.  If the given key is not a "leaf node"
	 *   then the return value will be an array.  If the given key is an empty string, this will be a copy of the
	 *   whole configuration data set.
	 */
	public function config($key, $default=null);

}
