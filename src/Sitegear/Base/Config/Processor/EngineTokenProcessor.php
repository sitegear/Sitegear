<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Config\Processor;

use Sitegear\Base\Engine\EngineInterface;
use Sitegear\Util\PhpSourceUtilities;

/**
 * Token processor that allows substitution of values by calling methods, either on the engine directly or on a module
 * retrieved from that engine.
 *
 * Tokens of the form "prefix:methodName(arg1, arg2)" will call `methodName()` on the engine.
 *
 * Tokens of the form "prefix:moduleName/methodName(arg1, arg2)" will call `methodName()` on the module with name given
 * by `moduleName`.
 */
class EngineTokenProcessor extends AbstractPrefixedTokenProcessor {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Engine\EngineInterface
	 */
	private $engine;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\Engine\EngineInterface $engine
	 * @param string $prefix
	 */
	public function __construct(EngineInterface $engine, $prefix) {
		parent::__construct($prefix);
		$this->engine = $engine;
	}

	//-- AbstractPrefixedTokenProcessor Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function getTokenResultReplacement($token) {
		$result = null;
		$matches = array();
		if (preg_match('/^([a-zA-Z][a-zA-Z0-9]*?)\\/(.*?\\(.*\\))$/', $token, $matches) && sizeof($matches) > 2) {
			$target = $this->engine->getModule($matches[1]);
			$call = $matches[2];
		} else {
			$target = $this->engine;
			$call = $token;
		}
		$parsed = PhpSourceUtilities::parseFunctionCall($call);
		$method = array( $target, $parsed['name'] );
		if (is_callable($method)) {
			$result = call_user_func_array($method, $parsed['arguments']);
		}
		return $result;
	}

}
