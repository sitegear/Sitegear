<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Config;

use Sitegear\Config\Processor\ProcessorInterface;
use Sitegear\Util\ArrayUtilities;
use Sitegear\Util\TypeUtilities;
use Sitegear\Util\LoggerRegistry;

/**
 * Configuration container, which stores and allows retrieval of configuration values.
 *
 * Values can be "merged" by passing nested array structures, and retrieved using dot-notation keys.
 *
 * For example, with a container that has an array like this merged to it:
 *
 * { 'foo': 'bar', 'baz': { 'daughter': 'value' } }
 *
 * Retrieving the key 'baz.daughter' will yield the result 'value', and retrieving the key 'baz' will yield the result
 * { 'daughter': 'value' }
 */
class Configuration {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Config\ConfigLoader
	 */
	private $loader;

	/**
	 * @var ProcessorInterface[]
	 */
	private $processors;

	/**
	 * @var mixed[]
	 */
	private $data;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Config\ConfigLoader $loader
	 */
	public function __construct(ConfigLoader $loader) {
		LoggerRegistry::debug('new Configuration()');
		$this->loader = $loader;
		$this->processors = array();
		$this->data = array();
	}

	//-- Public Methods --------------------

	/**
	 * @return ConfigLoader
	 */
	public function getLoader() {
		return $this->loader;
	}

	/**
	 * Add the given processor to the stack.
	 *
	 * @param \Sitegear\Config\Processor\ProcessorInterface $processor Processor to add.
	 *
	 * @return self
	 */
	public function addProcessor(ProcessorInterface $processor) {
		LoggerRegistry::debug(sprintf('Configuration::addProcessor(%s)', TypeUtilities::describe($processor)));
		$this->processors[] = $processor;
	}

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
	 * @return self
	 *
	 * @throws \InvalidArgumentException
	 */
	public function merge($config, $rootKey=null, $preferExisting=false) {
		LoggerRegistry::debug(sprintf('Configuration::merge(%s, %s, %s)', TypeUtilities::describe($config), $rootKey, $preferExisting ? 'true' : 'false'));
		// Load using the container's ConfigLoader.
		$data = $this->loader->load($config);
		// Push the data down the hierarchy to the specified root key.
		$rootKey = $this->normaliseKey($rootKey);
		while (!empty($rootKey)) {
			$k = array_pop($rootKey);
			$data = array( $k => $data );
		}
		// Combine the arrays depending on the $preferExisting flag.
		$this->data = $preferExisting ?
				ArrayUtilities::combine($data, $this->data) :
				ArrayUtilities::combine($this->data, $data);
		return $this;
	}

	/**
	 * Get the named configuration item.
	 *
	 * @param string|array $key Key to look up.  To inspect nested keys, use dot notation, or pass an array of
	 *   subsequent keys for each level of descent into the configuration array structure.
	 * @param mixed|null $default (optional) Default value, if the configuration does not contain the specified key.
	 *
	 * @return mixed Value associated with the given key.
	 */
	public function get($key, $default=null) {
		$keys = $this->normaliseKey($key);
		$value = $this->data;
		foreach ($keys as $k) {
			if (is_string($value)) {
				$value = $this->applyProcessors($value);
			}
			$value = (is_array($value) && isset($value[$k])) ? $value[$k] : null;
		}
		return $value ? $this->applyProcessors($value) : $default;
	}

	/**
	 * Get all configuration items as an array structure.
	 *
	 * @return array Top-level configuration array.
	 */
	public function all() {
		return $this->get('', array());
	}

	//-- Internal Methods --------------------

	/**
	 * Converts the given key to its array form.  This default implementation converts strings by splitting them on the
	 * dot "." character.
	 *
	 * @param mixed $key Key to convert.
	 *
	 * @return array Indexed array, where each element of the array is a subsequent child key.
	 */
	protected function normaliseKey($key) {
		return is_array($key) ? $key : ((isset($key) && !empty($key)) ? explode('.', $key) : array());
	}

	/**
	 * Apply processors to the given value, or all values in the given (possibly nested) array.
	 *
	 * @param mixed $value Value(s) to process.
	 *
	 * @return mixed Processed value(s).
	 */
	protected function applyProcessors($value) {
		// First apply all processors to any string value.
		if (is_string($value)) {
			foreach ($this->processors as $processor) {
				$value = $processor->process($value);
			}
		}
		// Now apply recursively to arrays; this is done second in case a string value is expanded in the first step.
		if (is_array($value)) {
			foreach ($value as $key => $item) {
				$value[$key] = $this->applyProcessors($item);
			}
		}
		return $value;
	}

}
