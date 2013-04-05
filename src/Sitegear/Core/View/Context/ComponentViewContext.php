<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\View\Context;

use Sitegear\Base\Module\ModuleInterface;
use Sitegear\Core\View\View;
use Sitegear\Util\NameUtilities;

/**
 * View context for rendering components.
 */
class ComponentViewContext extends AbstractCoreFileViewContext {

	//-- Constants --------------------

	const FORMAT_COMPONENT_CONTROLLER_METHOD_NAME = '%sComponent';

	//-- ViewContextInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 *
	 * This implementation returns the target name (in camelCase) plus 'Component'.
	 */
	public function getTargetController() {
		return array(
			$this->view()->getEngine()->getModule($this->getContextModule($this->request())),
			sprintf(self::FORMAT_COMPONENT_CONTROLLER_METHOD_NAME, NameUtilities::convertToCamelCase($this->view()->getTarget(View::TARGET_LEVEL_METHOD)))
		);
	}

	//-- AbstractFileViewContext Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function getContextModule() {
		$targetModule = $this->view()->getTarget(View::TARGET_LEVEL_MODULE);
		return $targetModule === View::SPECIAL_TARGET_MODULE_COMPONENT ?
				$this->view()->getEngine()->getDefaultContentModule() :
				$targetModule;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function expandViewScriptPaths($viewScriptName, $methodResult) {
		return array(
			sprintf('components/%s', $methodResult ?: $this->view()->getTarget(View::TARGET_LEVEL_METHOD))
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setupView($viewName) {
		/** @var ModuleInterface $module */
		parent::setupView($viewName);
		$module = $this->view()['module'];
		$module->applyViewDefaults($this->view(), 'components', $viewName);
	}
	
}
