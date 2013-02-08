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
 * View context for rendering module-specific content.
 */
class ModuleItemViewContext extends AbstractCoreFileViewContext {

	//-- AbstractCoreFileViewContext Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function getContextModule(ViewInterface $view, Request $request) {
		return $view->getTarget(View::TARGET_LEVEL_MODULE);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function expandViewScriptPaths($viewScriptName, ViewInterface $view, Request $request, $methodResult) {
		$args = $view->getTargetArguments(View::TARGET_LEVEL_METHOD);
		return array(
			$methodResult ?: (!empty($args) ? $args[0] : null)
		);
	}

}
