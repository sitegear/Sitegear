<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Context;

use Sitegear\Info\ResourceLocations;
use Sitegear\View\ViewInterface;
use Sitegear\View\Renderer\Registry\RendererRegistryInterface;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFileViewContext extends AbstractViewContext {

	//-- ViewContextInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function render(RendererRegistryInterface $rendererRegistry, $methodResult) {
		// If the preparation method returns false, don't output anything
		// If the preparation method returns a string, it overrides the target component name
		// If the preparation method doesn't exist or returns null or nothing, just display the original view script
		$result = null;
		if ($methodResult !== false) {
			$result = $this->renderForLocation(ResourceLocations::RESOURCE_LOCATION_SITE, $rendererRegistry, $methodResult);
			if (is_null($result)) {
				$result = $this->renderForLocation(ResourceLocations::RESOURCE_LOCATION_MODULE, $rendererRegistry, $methodResult);
			}
			if (is_null($result)) {
				$result = $this->renderForLocation(ResourceLocations::RESOURCE_LOCATION_ENGINE, $rendererRegistry, $methodResult);
			}
		}
		return $result;
	}

	//-- Internal Methods --------------------

	/**
	 * Try to render the specified file, returning the result or null if it cannot be rendered for this context.
	 *
	 * @param string $location
	 * @param \Sitegear\View\Renderer\Registry\RendererRegistryInterface $rendererRegistry
	 * @param mixed $methodResult
	 *
	 * @return null|string
	 */
	protected abstract function renderForLocation($location, RendererRegistryInterface $rendererRegistry, $methodResult);

	/**
	 * Get the module name for this view context.
	 *
	 * @return string Module name.
	 */
	protected abstract function getContextModule();

}
