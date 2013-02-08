<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\View\Context;

use Sitegear\Base\View\Resources\ResourceLocations;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Base\View\Renderer\Registry\RendererRegistryInterface;
use Sitegear\Core\View\View;

use Symfony\Component\HttpFoundation\Request;

/**
 * View context for rendering defined sections.
 */
class SectionViewContext extends AbstractCoreFileViewContext {

	//-- Attributes --------------------

	/**
	 * @var string
	 */
	private $indexSection;

	/**
	 * @var string
	 */
	private $fallbackSection;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param string $indexSection
	 * @param string $fallbackSection
	 */
	public function __construct(ViewInterface $view, $indexSection, $fallbackSection) {
		parent::__construct($view);
		$this->indexSection = $indexSection;
		$this->fallbackSection = $fallbackSection;
	}

	//-- ViewContextInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 *
	 * This implementation allows a global fallback, out-of-module, if no relevant content is found in the given
	 * module.
	 */
	public function render(RendererRegistryInterface $rendererRegistry, ViewInterface $view, Request $request, $methodResult) {
		$result = parent::render($rendererRegistry, $view, $request, $methodResult);
		$finalFallbackModule = $view->getEngine()->getDefaultContentModule();
		if (is_null($result) && $this->getContextModule($view, $request) !== $finalFallbackModule) {
			$finalFallback = sprintf('sections/%s/fallback', $view->getTarget(View::TARGET_LEVEL_METHOD));
			$finalFallback = $view->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_SITE, $finalFallbackModule, $finalFallback);
			$result = $rendererRegistry->render($finalFallback, $view);
		}
		return $result;
	}

	//-- AbstractFileViewContext Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function getContextModule(ViewInterface $view, Request $request) {
		return $request->attributes->get('_module');
	}

	/**
	 * {@inheritDoc}
	 *
	 * The $methodResult is intentionally ignored by this implementation.
	 */
	protected function expandViewScriptPaths($viewScriptName, ViewInterface $view, Request $request, $methodResult) {
		$result = array();
		foreach ($this->getSectionPathOptions($viewScriptName, $view, $request) as $option) {
			$result[] = sprintf('sections/%s/%s', $view->getTarget(View::TARGET_LEVEL_METHOD), $option);
		}
		return $result;
	}

	//-- Internal Methods --------------------

	/**
	 * Retrieve the view path options for the give view path, including all alternatives and fallback paths that should
	 * be attempted.  Specifically, the following options should be retrieved:
	 *
	 * ZERO ELEMENT PATHS ("", "/")
	 * + index
	 * + fallback
	 *
	 * SINGLE ELEMENT PATHS ("foo", "/foo", "/foo/")
	 * + foo/index
	 * + foo
	 * + foo/fallback
	 * + fallback
	 *
	 * TWO ELEMENT PATHS ("foo/bar", "/foo/bar", "/foo/bar/")
	 * + foo/bar/index
	 * + foo/bar
	 * + foo/bar/fallback
	 * + foo/fallback
	 * + fallback
	 *
	 * ...and so on.
	 *
	 * Note in the above, that "index" will actually be the value passed to $indexSection in the constructor, and
	 * "fallback" will actually be the value of $fallbackSection.
	 *
	 * @param string $original Original view path, of the form "foo/bar"; leading and trailing slashes are ignored.
	 * @param ViewInterface $view
	 * @param Request $request
	 *
	 * @return array Array of view paths that should be tried when rendering the specified original view path, in order
	 *   from the favourite option to the least favourite option.
	 */
	private function getSectionPathOptions($original, ViewInterface $view, Request $request) {
		$module = $view->getEngine()->getModule($this->getContextModule($view, $request)); /** @var \Sitegear\Base\Module\MountableModuleInterface $module */
		$rootUrl = $module->getMountedUrl();
		$original = str_replace($rootUrl, '', $original);
		$original = trim($original, '/');
		$options = array();
		if (strlen($original) === 0) {
			// Home page
			$options[] = $this->indexSection;
		} else {
			// Any other page, at any path level
			// First check [path]/index
			$options[] = sprintf('%s/%s', $original, $this->indexSection);
			// Next check [path]
			$options[] = $original;
			// Now check the fallback at every level, if not disabled; top level fallback is handled further below
			if (is_string($this->fallbackSection)) {
				$path = explode('/', $original);
				// Skip the top level, which produces unwanted results due to implode() behaviour
				while (sizeof($path) > 0) {
					$options[] = sprintf('%s/%s', implode('/', $path), $this->fallbackSection);
					array_pop($path);
				}

			}
		}
		if (is_string($this->fallbackSection)) {
			// Global fallback as the final option
			$options[] = $this->fallbackSection;
		}
		return $options;
	}

}
