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
 * Token processor that allows substitution of values from a specified configuration container.
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
		$parsed = PhpSourceUtilities::parseFunctionCall($token);
		$method = array( $this->engine, $parsed['name'] );
		if (is_callable($method)) {
			$result = call_user_func_array($method, $parsed['arguments']);
		}
		return $result;
	}

}
