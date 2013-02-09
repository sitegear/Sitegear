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

	//-- AbstractPrefixedTokenProcessor Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function getTokenResultReplacement($token) {
		if ($token === '*') {
			return $this->array;
		} else {
			return isset($this->array[$token]) ? $this->array[$token] : null;
		}
	}

}
