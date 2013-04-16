<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\StringsManager;

/**
 * Simple implementation of the StringsManagerInterface.
 */
class StringsManager implements StringsManagerInterface {

	//-- Constants --------------------

	/**
	 * Separator used by default, if no other is specified using setSeparator().
	 */
	const DEFAULT_SEPARATOR = ', ';

	//-- Attributes --------------------

	/**
	 * @var array[]
	 */
	private $map = array();

	//-- StringsManagerInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function append($key, $item) {
		$this->ensureKeyExists($key);
		for ($i=1; $i<func_num_args(); $i++) {
			array_push($this->map[$key]['items'], func_get_arg($i));
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function prepend($key, $item) {
		$this->ensureKeyExists($key);
		for ($i=1; $i<func_num_args(); $i++) {
			array_unshift($this->map[$key]['items'], func_get_arg($i));
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getSeparator($key) {
		return isset($this->map[$key]) ? ($this->map[$key]['separator'] ?: self::DEFAULT_SEPARATOR) : null;
	}

	/**
	 * @inheritdoc
	 */
	public function setSeparator($key, $separator) {
		$this->ensureKeyExists($key);
		$this->map[$key]['separator'] = $separator;
	}

	/**
	 * @inheritdoc
	 */
	public function getKeys() {
		return array_keys($this->map);
	}

	/**
	 * @inheritdoc
	 */
	public function render($key) {
		return isset($this->map[$key]) ? implode($this->getSeparator($key), $this->map[$key]['items']) : null;
	}

	//-- Internal Methods --------------------

	/**
	 * If the specified key does not exist, create an empty container for it.
	 *
	 * @param string $key
	 */
	protected function ensureKeyExists($key) {
		if (!isset($this->map[$key])) {
			$this->map[$key] = array(
				'items' => array(),
				'separator' => null
			);
		}
	}
}
