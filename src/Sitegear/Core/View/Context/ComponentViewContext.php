<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\View\Context;

use Sitegear\Base\Module\ModuleInterface;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Core\View\View;
use Sitegear\Util\NameUtilities;

use Symfony\Component\HttpFoundation\Request;

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
	public function getTargetController(ViewInterface $view, Request $request) {
		return array(
			$view->getEngine()->getModule($this->getContextModule($view, $request)),
			sprintf(self::FORMAT_COMPONENT_CONTROLLER_METHOD_NAME, NameUtilities::convertToCamelCase($view->getTarget(View::TARGET_LEVEL_METHOD)))
		);
	}

	//-- AbstractFileViewContext Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function getContextModule(ViewInterface $view, Request $request) {
		$targetModule = $view->getTarget(View::TARGET_LEVEL_MODULE);
		return $targetModule === View::SPECIAL_TARGET_MODULE_COMPONENT ?
				$view->getEngine()->getDefaultContentModule() :
				$targetModule;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function expandViewScriptPaths($viewScriptName, ViewInterface $view, Request $request, $methodResult) {
		return array(
			sprintf('components/%s', $methodResult ?: $view->getTarget(View::TARGET_LEVEL_METHOD))
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setupView(ViewInterface $view, Request $request, $viewName) {
		/** @var ModuleInterface $module */
		parent::setupView($view, $request, $viewName);
		$module = $view['module'];
		$module->applyViewDefaults($view, 'components', $viewName);
	}
	
}
