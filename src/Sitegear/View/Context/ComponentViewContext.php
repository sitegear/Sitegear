<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Context;

use Sitegear\Module\ModuleInterface;
use Sitegear\View\Context\AbstractSitegearFileViewContext;
use Sitegear\View\View;
use Sitegear\Util\NameUtilities;

/**
 * View context for rendering components.
 */
class ComponentViewContext extends AbstractSitegearFileViewContext {

	//-- Constants --------------------

	const FORMAT_COMPONENT_CONTROLLER_METHOD_NAME = '%sComponent';

	//-- ViewContextInterface Methods --------------------

	/**
	 * @inheritdoc
	 *
	 * This implementation returns the target name (in camelCase) plus 'Component'.
	 */
	public function getTargetController() {
		return array(
			$this->view()->getEngine()->getModule($this->getContextModule($this->request())),
			sprintf(self::FORMAT_COMPONENT_CONTROLLER_METHOD_NAME, NameUtilities::convertToCamelCase($this->view()->getTarget(\Sitegear\View\View::TARGET_LEVEL_METHOD)))
		);
	}

	//-- AbstractFileViewContext Methods --------------------

	/**
	 * @inheritdoc
	 */
	protected function getContextModule() {
		$targetModule = $this->view()->getTarget(\Sitegear\View\View::TARGET_LEVEL_MODULE);
		return $targetModule === \Sitegear\View\View::SPECIAL_TARGET_MODULE_COMPONENT ?
				$this->view()->getEngine()->getDefaultContentModule() :
				$targetModule;
	}

	/**
	 * @inheritdoc
	 */
	protected function expandViewScriptPaths($viewScriptName, $methodResult) {
		return array(
			sprintf('components/%s', $methodResult ?: $this->view()->getTarget(\Sitegear\View\View::TARGET_LEVEL_METHOD))
		);
	}

	/**
	 * @inheritdoc
	 */
	protected function setupView($viewName) {
		/** @var ModuleInterface $module */
		parent::setupView($viewName);
		$module = $this->view()['module'];
		$module->applyViewDefaults($this->view(), 'components', $viewName);
	}
	
}
