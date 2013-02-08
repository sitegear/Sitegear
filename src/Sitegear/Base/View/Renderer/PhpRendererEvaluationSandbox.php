<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\View\Renderer;

use Sitegear\Base\View\ViewInterface;

/**
 * Simple static class to provide a sandbox environment for rendering PHP view scripts.
 */
class PhpRendererEvaluationSandbox {

	/**
	 * Render the given view script.
	 *
	 * @param string $__viewScriptFilePath Absolute path to the view script PHP file to include.
	 * @param \Sitegear\Base\View\ViewInterface $view View context being rendered, made accessible within the view script.
	 *
	 * @return string Rendered content.
	 */
	public static function render($__viewScriptFilePath, /** @noinspection PhpUnusedParameterInspection */ ViewInterface $view) {
		ob_start();
		/** @noinspection PhpIncludeInspection */
		include $__viewScriptFilePath;
		return ob_get_clean();
	}

}
