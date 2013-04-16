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
use Sitegear\View\Factory\SitegearViewFactory;
use Sitegear\View\View;
use Sitegear\Util\NameUtilities;
use Sitegear\View\ViewInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * View context for rendering components.
 */
class ComponentViewContext extends AbstractSitegearFileViewContext {

	//-- Constants --------------------

	const FORMAT_COMPONENT_CONTROLLER_METHOD_NAME = '%sComponent';

	//-- Attributes --------------------

	private $useDefaultContentModule;

	//-- Constructor --------------------

	public function __construct(ViewInterface $view, Request $request, $useDefaultContentModule) {
		parent::__construct($view, $request);
		$this->useDefaultContentModule = $useDefaultContentModule;
	}

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
		return $this->useDefaultContentModule ?
				$this->view()->getEngine()->getDefaultContentModule() :
				$this->view()->getTarget(View::TARGET_LEVEL_MODULE);
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
