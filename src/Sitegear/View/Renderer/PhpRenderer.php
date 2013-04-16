<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Renderer;

use Sitegear\View\Renderer\RendererInterface;
use Sitegear\View\ViewInterface;
use Sitegear\Util\TypeUtilities;
use Sitegear\Util\LoggerRegistry;

/**
 * RendererInterface implementation which renders .phtml / .php files.
 */
class PhpRenderer implements RendererInterface {

	//-- RendererInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function supports($path) {
		$supported = false;
		foreach ($this->getExtensions() as $extension) {
			$supported = $supported || file_exists($path . $extension);
		}
		return $supported;
	}

	/**
	 * @inheritdoc
	 */
	public function render($path, ViewInterface $view) {
		LoggerRegistry::debug(sprintf('PhpRenderer::render(%s, %s)', $path, TypeUtilities::describe($view)));
		$renderPath = null;
		foreach ($this->getExtensions() as $extension) {
			if (is_null($renderPath) && file_exists($path . $extension)) {
				$renderPath = $path . $extension;
			}
		}
		if (is_null($renderPath)) {
			throw new \InvalidArgumentException(sprintf('The path "%s" cannot be rendered by PhpRenderer', $path));
		}
		return PhpRendererEvaluationSandbox::render($renderPath, $view);
	}

	//-- Internal Methods --------------------

	/**
	 * @return array
	 */
	protected function getExtensions() {
		return array( '.phtml', '.html.php' );
	}

}
