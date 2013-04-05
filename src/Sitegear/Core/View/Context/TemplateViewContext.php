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
use Sitegear\Util\TypeUtilities;

/**
 * View context for rendering page (top-level) templates.
 */
class TemplateViewContext extends AbstractCoreFileViewContext {

	//-- AbstractFileViewContext Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function getContextModule() {
		return $this->view()->getEngine()->getDefaultContentModule();
	}

	/**
	 * {@inheritDoc}
	 */
	public function expandViewScriptPaths($viewScriptName, $methodResult) {
		return array(
			sprintf('templates/%s', ($methodResult ?: $this->view()->getTarget(View::TARGET_LEVEL_METHOD)))
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setupView($viewName) {
		/** @var ModuleInterface $module */
		parent::setupView($viewName);
		$module = $this->request()->attributes->get('_module');
		$view = $this->request()->attributes->get('_view');
		$this->view()->getEngine()->getModule($module)->applyViewDefaults($this->view(), 'pages', $view);
	}

}
