<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\View\Context;

use Sitegear\Base\View\Context\AbstractFileViewContext;
use Sitegear\Base\View\Renderer\Registry\RendererRegistryInterface;
use Sitegear\Core\View\View;

/**
 * Provides an abstract view context implementation for file-based contexts in the context of a Sitegear Core
 * application.  That is, it renders from any of the three core roots (module, engine or site) using the '_view'
 * Request attribute, which is set by the controller resolver.
 */
abstract class AbstractCoreFileViewContext extends AbstractFileViewContext {

	//-- AbstractFileViewContext Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function renderForLocation($location, RendererRegistryInterface $rendererRegistry, $methodResult) {
		$result = null;
		$this->setupView($methodResult ?: $this->view()->getTarget(View::TARGET_LEVEL_METHOD));
		foreach ($this->expandViewScriptPaths(trim($this->request()->attributes->get('_view'), '/'), $methodResult) as $path) {
			if (is_null($result)) {
				$sitePath = $this->view()->getEngine()->getSiteInfo()->getSitePath($location, $path, $this->view()['module']);
				$result = $rendererRegistry->render($sitePath, $this->view());
			}
		}
		return $result;
	}

	//-- Internal Methods --------------------

	/**
	 * Find the path or paths within any location root where the path specified may be found within this context.  Note
	 * that these are not full paths, only paths relative to either the module's built-in root or the context module's
	 * site-specific root.
	 *
	 * @param string $viewScriptName
	 * @param mixed $methodResult
	 *
	 * @return array One or more resolved locations.
	 */
	protected abstract function expandViewScriptPaths($viewScriptName, $methodResult);

	/**
	 * Setup the view with a reference to the module and any other context-specific data.
	 *
	 * @param string $viewName
	 */
	protected function setupView($viewName) {
		$this->view()['module'] = $this->view()->getEngine()->getModule($this->getContextModule($this->request()));
	}

}
