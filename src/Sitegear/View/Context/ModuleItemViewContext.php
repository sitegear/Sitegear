<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Context;

use Sitegear\View\Context\AbstractSitegearFileViewContext;
use Sitegear\View\View;

/**
 * View context for rendering module-specific content.
 */
class ModuleItemViewContext extends AbstractSitegearFileViewContext {

	//-- AbstractCoreFileViewContext Methods --------------------

	/**
	 * @inheritdoc
	 */
	protected function getContextModule() {
		return $this->view()->getTarget(View::TARGET_LEVEL_MODULE);
	}

	/**
	 * @inheritdoc
	 */
	protected function expandViewScriptPaths($viewScriptName, $methodResult) {
		$args = $this->view()->getTargetArguments(View::TARGET_LEVEL_METHOD);
		return array(
			$methodResult ?: (!empty($args) ? $args[0] : null)
		);
	}

}
