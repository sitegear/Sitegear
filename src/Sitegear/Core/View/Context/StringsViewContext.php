<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\View\Context;

use Sitegear\Base\View\Context\AbstractViewContext;
use Sitegear\Base\View\Renderer\Registry\RendererRegistryInterface;
use Sitegear\Core\View\Decorator\StringTokensDecorator;
use Sitegear\Core\View\View;

/**
 * View context for rendering strings, using placeholder tokens to allow the strings to be collected throughout the rendering
 * process, and replaced with a final value at the end of the rendering cycle.
 */
class StringsViewContext extends AbstractViewContext {

	//-- ViewContextInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 *
	 * See class documentation.
	 */
	public function render(RendererRegistryInterface $rendererRegistry, $methodResult) {
		$key = $this->view()->getTarget(View::TARGET_LEVEL_METHOD);
		$arguments = $this->view()->getTargetArguments(View::TARGET_LEVEL_METHOD);
		if (!empty($arguments)) {
			$this->view()->getEngine()->getViewFactory()->getStringsManager()->setSeparator($key, $arguments[0]);
		}
		return StringTokensDecorator::token($key);
	}

}
