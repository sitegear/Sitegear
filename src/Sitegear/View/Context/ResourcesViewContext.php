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
use Sitegear\View\Decorator\ResourceTokensDecorator;
use Sitegear\View\View;

/**
 * View context for rendering resources.  What this actually means is, rendering tokens from ResourceTokensDecorator,
 * using its static token() method, which will be later parsed and replaced with actual resource references.  The token
 * type argument value is taken from the last added target in the specified view.
 */
class ResourcesViewContext extends AbstractViewContext {

	//-- ViewContextInterface Methods --------------------

	/**
	 * @inheritdoc
	 *
	 * See class documentation.
	 */
	public function render(RendererRegistryInterface $rendererRegistry, $methodResult) {
		return ResourceTokensDecorator::token($this->view()->getTarget(\Sitegear\View\View::TARGET_LEVEL_METHOD));
	}

}
