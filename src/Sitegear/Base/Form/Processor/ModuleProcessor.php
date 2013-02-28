<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Processor;

use Sitegear\Base\Module\ModuleInterface;
use Sitegear\Util\ArrayUtilities;

/**
 * FormProcessorInterface implementation which calls a specified method in a specified module object.
 */
class ModuleProcessor extends AbstractFormProcessor {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Module\ModuleInterface
	 */
	private $module;

	/**
	 * @var string
	 */
	private $methodName;

	//-- Constructor --------------------

	/**
	 * @param array|null $argumentDefaults
	 * @param string[]|null $exceptionFieldNames
	 * @param string|null $exceptionAction
	 * @param \Sitegear\Base\Module\ModuleInterface $module
	 * @param string $methodName
	 */
	public function __construct(ModuleInterface $module, $methodName, array $argumentDefaults=null, array $exceptionFieldNames=null, $exceptionAction=null) {
		parent::__construct($argumentDefaults, $exceptionFieldNames, $exceptionAction);
		$this->module = $module;
		$this->methodName = $methodName;
	}

	//-- FormProcessorInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getProcessorMethod() {
		return array($this->getModule(), $this->getMethodName());
	}

	//-- Public Methods --------------------

	/**
	 * @return \Sitegear\Base\Module\ModuleInterface
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
