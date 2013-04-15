<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Form\Processor;

use Sitegear\Module\ModuleInterface;
use Sitegear\Util\ArrayUtilities;
use Sitegear\Util\NameUtilities;

/**
 * FormProcessorInterface implementation which calls a specified method in a specified module object.
 */
class ModuleProcessor extends AbstractFormProcessor {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Module\ModuleInterface
	 */
	private $module;

	/**
	 * @var string
	 */
	private $methodName;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Module\ModuleInterface $module
	 * @param string $methodName
	 * @param array|null $argumentDefaults
	 * @param string|null $exceptionAction
	 */
	public function __construct(ModuleInterface $module, $methodName, array $argumentDefaults=null, $exceptionAction=null) {
		parent::__construct($argumentDefaults, $exceptionAction);
		$this->module = $module;
		$this->methodName = NameUtilities::convertToCamelCase($methodName);
	}

	//-- FormProcessorInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getProcessorMethod() {
		return array($this->getModule(), $this->getMethodName());
	}

	//-- Public Methods --------------------

	/**
	 * @return \Sitegear\Module\ModuleInterface
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * @return string
	 */
	public function getMethodName() {
		return $this->methodName;
	}

}
