<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Config\Processor;

use Sitegear\Base\Config\ConfigurableInterface;

/**
 * Token processor that allows substitution of values from a specified configuration container.
 */
class ConfigTokenProcessor extends AbstractPrefixedTokenProcessor {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Config\Container\ConfigContainerInterface
	 */
	private $object;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\Config\ConfigurableInterface $object
	 * @param string $prefix
	 */
	public function __construct(ConfigurableInterface $object, $prefix) {
		parent::__construct($prefix);
		$this->object = $object;
	}

	//-- AbstractPrefixedTokenProcessor Methods --------------------

	/**
	 * @inheritdoc
	 */
	protected function getTokenResultReplacement($token) {
		return $this->object->config($token);
	}

}
