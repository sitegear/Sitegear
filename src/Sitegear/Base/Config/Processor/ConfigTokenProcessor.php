<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Config\Processor;

use Sitegear\Base\Config\Container\ConfigContainerInterface;

/**
 * Token processor that allows substitution of values from a specified configuration container.
 */
class ConfigTokenProcessor extends AbstractPrefixedTokenProcessor {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Config\Container\ConfigContainerInterface
	 */
	private $config;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\Config\Container\ConfigContainerInterface $config
	 * @param string $prefix
	 */
	public function __construct(ConfigContainerInterface $config, $prefix) {
		parent::__construct($prefix);
		$this->config = $config;
	}

	//-- AbstractPrefixedTokenProcessor Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function getTokenResultReplacement($token) {
		return $this->config->get($token);
	}

}
