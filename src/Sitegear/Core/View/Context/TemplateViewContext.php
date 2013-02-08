<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\View\Context;

use Sitegear\Core\View\View;
use Sitegear\Base\View\ViewInterface;

use Symfony\Component\HttpFoundation\Request;

/**
 * View context for rendering page (top-level) templates.
 */
class TemplateViewContext extends AbstractCoreFileViewContext {

	//-- AbstractFileViewContext Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function getContextModule(ViewInterface $view, Request $request) {
		return $view->getEngine()->getDefaultContentModule();
	}

	/**
	 * {@inheritDoc}
	 */
	public function expandViewScriptPaths($viewScriptName, ViewInterface $view, Request $request, $methodResult) {
		return array(
			sprintf('templates/%s', ($methodResult ?: $view->getTarget(View::TARGET_LEVEL_METHOD)))
		);
	}

}
