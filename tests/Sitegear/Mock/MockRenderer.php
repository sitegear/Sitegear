<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Mock;

use Sitegear\View\Renderer\RendererInterface;
use Sitegear\View\ViewInterface;

/**
 * Mock RendererInterface implementation, used only to test registerRenderer().
 */
class MockRenderer implements RendererInterface {

	public function supports($path) {
		return $path !== '/do/not/render';
	}

	public function render($path, ViewInterface $view) {
		return '"' . $path . '" rendered by MockRenderer';
	}

}
