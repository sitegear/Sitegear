<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Config\Container;

use Sitegear\Base\Config\ConfigLoader;
use Sitegear\Base\Config\Processor\ProcessorInterface;
use Sitegear\Util\ArrayUtilities;
use Sitegear\Util\TypeUtilities;
use Sitegear\Util\LoggerRegistry;

/**
 * Simple implementation of the ConfigInterface, which simply stores and retrieves the values, but provides a hook for
 * further post-processing by sub-classes.  Actually loading the data is handled by the ConfigLoader singleton.
 */
class SimpleConfigContainer implements ConfigContainerInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Config\ConfigLoader
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
	 * @param \Sitegear\Base\Config\ConfigLoader|null $loader
	 */
	public function __construct(ConfigLoader $loader=null) {
		LoggerRegistry::debug('Instantiating SimpleConfigContainer');
		$this->loader = $loader ?: new ConfigLoader();
		$this->processors = array();
		$this->data = array();
	}

	//-- ConfigInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function addProcessor(ProcessorInterface $processor) {
		$this->processors[] = $processor;
	}

	/**
	 * {@inheritDoc}
	 */
	public function merge($config, $rootKey=null, $preferExisting=false) {
		LoggerRegistry::debug(sprintf('SimpleConfigContainer merging data [%s]', TypeUtilities::describe($config)));

		// Load using the container's ConfigLoader
		$data = $this->loader->load($config);

		// Push the data down the hierarchy to the specified root key
		$rootKey = $this->normaliseKey($rootKey);
		while (!empty($rootKey)) {
			$k = array_pop($rootKey);
			$data = array( $k => $data );
		}
		$this->data = $preferExisting ?
				ArrayUtilities::combine($data, $this->data) :
				ArrayUtilities::combine($this->data, $data);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get($key, $default=null) {
		$keys = $this->normaliseKey($key);
		$value = $this->data;
		foreach ($keys as $k) {
			$value = (is_array($value) && isset($value[$k])) ? $value[$k] : null;
		}
		return is_null($value) ? $default : $this->applyProcessors($value);
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
