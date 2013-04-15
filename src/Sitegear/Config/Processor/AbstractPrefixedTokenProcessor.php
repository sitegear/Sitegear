<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Config\Processor;

/**
 * Extends AbstractTokenProcessor by detecting tokens with a form like "prefix:token", checking for a prefix matching
 * the processor's configured prefix (as passed to the constructor), and replacing any matching tokens.
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
	 * @inheritdoc
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
