<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\View\Context;

use Sitegear\Base\View\Resources\ResourceLocations;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Base\View\Renderer\Registry\RendererRegistryInterface;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFileViewContext extends AbstractViewContext {

	//-- ViewContextInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function render(RendererRegistryInterface $rendererRegistry, ViewInterface $view, Request $request, $methodResult) {
		// If the preparation method returns false, don't output anything
		// If the preparation method returns a string, it overrides the target component name
		// If the preparation method doesn't exist or returns null or nothing, just display the original view script
		$result = null;
		if ($methodResult !== false) {
			$result = $this->renderForLocation(ResourceLocations::RESOURCE_LOCATION_SITE, $rendererRegistry, $view, $request, $methodResult);
			if (is_null($result)) {
				$result = $this->renderForLocation(ResourceLocations::RESOURCE_LOCATION_MODULE, $rendererRegistry, $view, $request, $methodResult);
			}
			if (is_null($result)) {
				$result = $this->renderForLocation(ResourceLocations::RESOURCE_LOCATION_ENGINE, $rendererRegistry, $view, $request, $methodResult);
			}
		}
		return $result;
	}

	//-- Internal Methods --------------------

	/**
	 * Try to render the specified file, returning the result or null if it cannot be rendered for this context.
	 *
	 * @param string $location
	 * @param \Sitegear\Base\View\Renderer\Registry\RendererRegistryInterface $rendererRegistry
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param mixed $methodResult
	 *
	 * @return null|string
	 */
	protected abstract function renderForLocation($location, RendererRegistryInterface $rendererRegistry, ViewInterface $view, Request $request, $methodResult);

	/**
	 * Get the module name for this view context.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return string Module name.
	 */
	protected abstract function getContextModule(ViewInterface $view, Request $request);

}
