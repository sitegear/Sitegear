<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Config\Processor;

/**
 * Allows tokens to be replaced by values from a data array passed to its constructor, using the token as the key from
 * which to retrieve its replacement.  For example, the token "foo" will be replaced by the value associated with the
 * key "foo" in the data array.  The special token "*" is replaced with the entire array.
 */
class ArrayTokenProcessor extends AbstractPrefixedTokenProcessor {

	//-- Constants --------------------

	const TOKEN_ALL_DATA = '*';

	//-- Attributes --------------------

	/**
	 * @var array
	 */
	private $array;

	//-- Constructor --------------------

	/**
	 * @param array $array
	 * @param string $prefix
	 */
	public function __construct(array $array, $prefix) {
		parent::__construct($prefix);
		$this->array = $array;
	}

	//-- Public Methods --------------------

	/**
	 * Get a copy of the data array.
	 *
	 * @return array
	 */
	public function getArray() {
		return $this->array;
	}

	/**
	 * Change the data array.
	 *
	 * @param array $array
	 */
	public function setArray(array $array) {
		$this->array = $array;
	}

	//-- AbstractPrefixedTokenProcessor Methods --------------------

	/**
	 * @inheritdoc
	 */
	protected function getTokenResultReplacement($token) {
		if ($token === self::TOKEN_ALL_DATA) {
			return $this->array;
		} else {
			return isset($this->array[$token]) ? $this->array[$token] : null;
		}
	}

}
