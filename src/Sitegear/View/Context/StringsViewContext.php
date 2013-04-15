<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Context;

use Sitegear\View\Context\AbstractViewContext;
use Sitegear\View\Renderer\Registry\RendererRegistryInterface;
use Sitegear\View\Decorator\StringTokensDecorator;
use Sitegear\View\View;

/**
 * View context for rendering strings, using placeholder tokens to allow the strings to be collected throughout the rendering
 * process, and replaced with a final value at the end of the rendering cycle.
 */
class StringsViewContext extends AbstractViewContext {

	//-- ViewContextInterface Methods --------------------

	/**
	 * @inheritdoc
	 *
	 * See class documentation.
	 */
	public function render(RendererRegistryInterface $rendererRegistry, $methodResult) {
		$key = $this->view()->getTarget(\Sitegear\View\View::TARGET_LEVEL_METHOD);
		$arguments = $this->view()->getTargetArguments(\Sitegear\View\View::TARGET_LEVEL_METHOD);
		if (!empty($arguments)) {
			$this->view()->getEngine()->getViewFactory()->getStringsManager()->setSeparator($key, $arguments[0]);
		}
		return StringTokensDecorator::token($key);
	}

}
