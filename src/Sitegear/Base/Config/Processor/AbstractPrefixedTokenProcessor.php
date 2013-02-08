<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Config\Processor;

/**
 * Abstract processor implementation which detects tokens like "${ token }" and provides a standard method which should
 * return the replacement value.
 */
abstract class AbstractPrefixedTokenProcessor extends AbstractTokenProcessor {

	//-- Attributes --------------------

	/**
	 * @var string
	 */
	private $prefix;

	//-- Constructor --------------------

	/**
	 * @param string $prefix
	 */
	public function __construct($prefix) {
		$this->prefix = $prefix;
	}

	//-- AbstractTokenProcessor Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function replaceToken($token) {
		$result = null;
		$matches = array();
		if (preg_match('/^' . $this->getPrefix() . '\\:(.*)$/', $token, $matches) && sizeof($matches) > 1) {
			$result = $this->getTokenResultReplacement($matches[1]);
		}
		return $result;
	}

	//-- Internal Methods --------------------

	/**
	 * @return string Prefix to use when detecting a token like "prefix:result".
	 */
	protected function getPrefix() {
		return $this->prefix;
	}

	/**
	 * Get a replacement for the given token result (i.e. the result part of "prefix:result").
	 *
	 * @param string $token Token to get replacement for.
	 *
	 * @return mixed Replacement value.
	 */
	protected abstract function getTokenResultReplacement($token);

}
