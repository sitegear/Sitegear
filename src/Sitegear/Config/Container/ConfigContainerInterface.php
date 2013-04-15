<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Config\Container;

use Sitegear\Config\Processor\ProcessorInterface;

/**
 * Defines the behaviour of a configuration container, which stores and allows retrieval of configuration values.
 *
 * Values can be "merged" by passing nested array structures, and retrieved using dot-notation keys.
 *
 * For example, with a container that has an array like this merged to it:
 *
 * { 'foo': 'bar', 'baz': { 'daughter': 'value' } }
 *
 * Retrieving the key 'baz.daughter' will yield the result 'value'.
 */
interface ConfigContainerInterface {

	/**
	 * Add the given processor to the stack.
	 *
	 * @param \Sitegear\Config\Processor\ProcessorInterface $processor Processor to add.
	 *
	 * @return self
	 */
	public function addProcessor(ProcessorInterface $processor);

	/**
	 * Load the given configuration file, and its relative environment-specific file (see class docs), and merge the
	 * contents with the existing configuration.
	 *
	 * @param string|array $config The base filename to load, or an array of values to merge in directly.
	 * @param null|string|array $rootKey The root key to merge the values to, will be created if it does not exist. If
	 *   omitted, the values will be merged to the root of the existing config.
	 * @param boolean $preferExisting Whether to prefer values from the passed-in config over the existing values (the
	 *   default) or to prefer existing values over passed-in values (if this argument is true).
	 *
	 * @return \Sitegear\Config\Container\ConfigContainerInterface Current object, fluent pattern.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function merge($config, $rootKey=null, $preferExisting=false);

	/**
	 * Get the named configuration item.
	 *
	 * @param string|array $key Key to look up.  To inspect nested keys, use dot notation, or pass an array of
	 *   subsequent keys for each level of descent into the configuration array structure.
	 * @param mixed|null $default (optional) Default value, if the configuration does not contain the specified key.
	 *
	 * @return mixed Value associated with the given key.
	 */
	public function get($key, $default=null);

	/**
	 * Get all configuration items as an array structure.
	 *
	 * @return array Top-level configuration array.
	 */
	public function all();

}
