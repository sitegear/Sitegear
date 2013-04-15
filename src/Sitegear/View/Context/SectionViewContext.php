<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Context;

use Sitegear\Info\ResourceLocations;
use Sitegear\View\Context\AbstractSitegearFileViewContext;
use Sitegear\View\ViewInterface;
use Sitegear\View\Renderer\Registry\RendererRegistryInterface;
use Sitegear\View\View;

use Symfony\Component\HttpFoundation\Request;

/**
 * View context for rendering defined sections.
 */
class SectionViewContext extends AbstractSitegearFileViewContext {

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
	 * @param \Sitegear\View\ViewInterface $view
	 * @param Request $request
	 * @param string $indexSection
	 * @param string $fallbackSection
	 */
	public function __construct(ViewInterface $view, Request $request, $indexSection, $fallbackSection) {
		parent::__construct($view, $request);
		$this->indexSection = $indexSection;
		$this->fallbackSection = $fallbackSection;
	}

	//-- ViewContextInterface Methods --------------------

	/**
	 * @inheritdoc
	 *
	 * This implementation allows a global fallback, out-of-module, if no relevant content is found in the given
	 * module.
	 */
	public function render(RendererRegistryInterface $rendererRegistry, $methodResult) {
		$result = parent::render($rendererRegistry, $methodResult);
		$finalFallbackModule = $this->view()->getEngine()->getModule($this->view()->getEngine()->getDefaultContentModule());
		if (is_null($result) && $this->getContextModule() !== $finalFallbackModule) {
			$finalFallback = sprintf('sections/%s/fallback', $this->view()->getTarget(\Sitegear\View\View::TARGET_LEVEL_METHOD));
			$finalFallback = $this->view()->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_SITE, $finalFallback, $finalFallbackModule);
			$result = $rendererRegistry->render($finalFallback, $this->view());
		}
		return $result;
	}

	//-- AbstractFileViewContext Methods --------------------

	/**
	 * @inheritdoc
	 */
	protected function getContextModule() {
		return $this->request()->attributes->get('_module');
	}

	/**
	 * @inheritdoc
	 *
	 * The $methodResult is intentionally ignored by this implementation.
	 */
	protected function expandViewScriptPaths($viewScriptName, $methodResult) {
		$result = array();
		foreach ($this->getSectionPathOptions($viewScriptName, $this->request()) as $option) {
			$result[] = sprintf('sections/%s/%s', $this->view()->getTarget(\Sitegear\View\View::TARGET_LEVEL_METHOD), $option);
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
	 *
	 * @return array Array of view paths that should be tried when rendering the specified original view path, in order
	 *   from the favourite option to the least favourite option.
	 */
	private function getSectionPathOptions($original) {
		/** @var \Sitegear\Module\MountableModuleInterface $module */
		$module = $this->view()->getEngine()->getModule($this->getContextModule($this->view(), $this->request()));
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
