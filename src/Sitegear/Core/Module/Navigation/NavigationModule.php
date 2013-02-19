<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\Navigation;

use Sitegear\Base\Module\AbstractConfigurableModule;
use Sitegear\Base\Module\MountableModuleInterface;
use Sitegear\Base\Engine\EngineInterface;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;

/**
 * Consumes navigation data provided by various NavigationProviderInterface implementations, and presents the combined
 * data in a range of formats.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class NavigationModule extends AbstractConfigurableModule {

	//-- Attributes --------------------

	private $data;

	//-- Constructor --------------------

	public function __construct(EngineInterface $engine) {
		parent::__construct($engine);
		$this->data = array();
	}

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Navigation';
	}

	//-- Component Target Controller Methods --------------------

	/**
	 * Display the navigation component.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param array $path
	 * @param null $maxDepth
	 * @param int $indent
	 */
	public function navigationComponent(ViewInterface $view, Request $request, array $path=null, $maxDepth=null, $indent=0) {
		LoggerRegistry::debug('NavigationModule::navigationComponent');
		$this->applyConfigToView('components.navigation', $view);
		$data = $this->getData(MountableModuleInterface::NAVIGATION_DATA_MODE_MAIN);
		$path = $path ?: array();
		foreach ($path as $index) {
			$data = $data[$index]['children'];
		}
		$view['data'] = $data;
		$view['url'] = trim($request->getPathInfo(), '/');
		$view['path'] = $path;
		$view['max-depth'] = $maxDepth;
		$view['indent'] = $indent;
	}

	/**
	 * Display the breadcrumbs navigation helper component.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param string|null $url Allows breadcrumb to be displayed for a different page; if null, the URL is taken from
	 *   the passed-in Request object.
	 *
	 * @return null|boolean
	 */
	public function breadcrumbsComponent(ViewInterface $view, Request $request, $url=null) {
		LoggerRegistry::debug('NavigationModule::breadcrumbsComponent');
		$this->applyConfigToView('components.breadcrumbs', $view);
		$url = trim(!is_null($url) ? $url : $request->getPathInfo(), '/');
		if (empty($url) && !$this->config('components.breadcrumbs.show-on-homepage')) {
			// This is the home page, and we are configured not to show the breadcrumbs here.
			return false;
		}
		$data = $this->getData(MountableModuleInterface::NAVIGATION_DATA_MODE_EXPANDED);
		$path = $this->getNavigationPath($url, $data);
		if (is_null($path)) {
			// The URL cannot be found in the navigation, don't show the breadcrumb because there is nothing to show.
			return false;
		}
		$trail = array();
		$breadcrumbKeys = array( 'url' => true, 'label' => true );
		if (strlen($url) > 0 && $this->config('components.breadcrumbs.prepend-homepage')) {
			$trail[] = array_intersect_key($this->getNavigationItem('', $data), $breadcrumbKeys);
		}
		foreach ($path as $pathItem) {
			$trail[] = array_intersect_key($data[$pathItem], $breadcrumbKeys);
			$data = isset($data[$pathItem]['children']) ? $data[$pathItem]['children'] : array();
		}
		$view['trail'] = $trail;
		return null;
	}

	//-- Public Methods --------------------

	/**
	 * Determine if the given `$url` is equal to or an ancestor of (according to the navigation structure) the given
	 * `$markUrl`.
	 *
	 * @param string $url
	 * @param string $markUrl
	 *
	 * @return boolean
	 */
	public function isCurrentOrAncestorUrl($url, $markUrl) {
		$result = false;
		$data = $this->getData(MountableModuleInterface::NAVIGATION_DATA_MODE_EXPANDED);
		$urlPath = $this->getNavigationPath($url, $data);
		$markUrlPath = $this->getNavigationPath($markUrl, $data);
		foreach ($markUrlPath ?: array() as $elem) {
			$result = $result || (array_shift($urlPath) === $elem);
		}
		return $result && empty($urlPath);
	}

	/**
	 * Force the navigation data to be regenerated the next time it is required.
	 *
	 * This method should be called whenever any item is changed which will affect navigation of the relevant type.
	 *
	 * If `$type` is null (the default) then all types will be cleared and recached.  Otherwise it must be one of the
	 * NAVIGATION_DATA_MODE_* constants defined by MountableModuleInterface.
	 *
	 * @param integer|null $mode
	 *
	 * @throws \InvalidArgumentException
	 */
	public function recache($mode=null) {
		$modes = array( MountableModuleInterface::NAVIGATION_DATA_MODE_MAIN, MountableModuleInterface::NAVIGATION_DATA_MODE_EXPANDED );
		if (!is_null($mode)) {
			if (in_array($mode, $modes)) {
				$modes = array( $mode );
			} else {
				throw new \InvalidArgumentException(sprintf('NavigationModule cannot recache on unknown mode value "%s"', $mode));
			}
		}
		foreach ($modes as $mode) {
			$this->getEngine()->getMemcached()->delete($this->getCacheKey($mode));
		}
	}

	//-- Internal Methods --------------------

	/**
	 * Get the memcached key for navigation data in the given mode.
	 *
	 * @param integer $mode
	 *
	 * @return string
	 */
	private function getCacheKey($mode) {
		return sprintf('navigation.data.%s', $mode);
	}

	/**
	 * Retrieve normalised navigation data, which is loaded and cached the first time it is required.
	 *
	 * @param integer $mode
	 *
	 * @return array
	 *
	 * @throws \DomainException If the default content module does not provide navigation data.
	 */
	private function getData($mode) {
		if (!isset($this->data[$mode])) {
			$cacheKey = $this->getCacheKey($mode);
			$cacheValue = $this->getEngine()->getMemcached()->get($cacheKey);
			if ($cacheValue) {
				$this->data[$mode] = $cacheValue;
			} else {
				$rootModule = $this->getEngine()->getModule($this->getEngine()->getDefaultContentModule());
				if (!$rootModule instanceof MountableModuleInterface) {
					throw new \DomainException(sprintf('Invalid module "%s" specified as default content module, does not provide navigation data, must implement MountableModuleInterface.', $this->getEngine()->getDefaultContentModule()));
				}
				$this->data[$mode] = $this->normaliseNavigation($rootModule->getNavigationData($mode), $mode);
				$this->getEngine()->getMemcached()->set($cacheKey, $this->data[$mode]);
			}
		}
		return $this->data[$mode];
	}

	/**
	 * Normalise navigation data by expanding 'children' nodes and prepending this module's root URL.
	 *
	 * @param mixed $navigation
	 * @param int $mode
	 *
	 * @return array
	 */
	private function normaliseNavigation($navigation, $mode) {
		if (!is_array($navigation)) {
			$navigation = array();
		} else {
			foreach ($navigation as $index => $item) {
				if (isset($item['children'])) {
					$navigation[$index]['children'] = $this->normaliseNavigation($item['children'], $mode);
				} elseif (strlen(trim($item['url'], '/')) > 0) {
					$moduleName = isset($item['module']) ? $item['module'] : $this->getEngine()->getModuleForUrl($item['url']);
					if (!empty($moduleName)) {
						$module = $this->getEngine()->getModule($moduleName); /** @var \Sitegear\Base\Module\MountableModuleInterface $module */
						$navigation[$index]['children'] = $this->normaliseNavigation($module->getNavigationData($mode), $mode);
					}
				}
				if (empty($navigation[$index]['children'])) {
					unset($navigation[$index]['children']);
				}
			}
		}
		return $navigation;
	}

	/**
	 * Determine, using the compiled navigation, the path to the given URL as an array of indexes in the navigation
	 * data at subsequent levels.
	 *
	 * For example, if the navigation data is: { a, b, c: { d, e: { f }, g: { h, i } }, j }, then the following results
	 * will be returned:
	 *
	 *  * a = [ 0 ]
	 *  * c = [ 2 ]
	 *  * d = [ 2, 0 ]
	 *  * f = [ 2, 1, 0 ]
	 *  * i = [ 2, 2, 1 ]
	 *  * j = [ 3 ]
	 *  * z = null
	 *
	 * @param string $url URL to lookup
	 * @param array $data
	 *
	 * @return integer[]|null
	 */
	private function getNavigationPath($url, $data) {
		$result = null;
		// First check if it is at this level, if so we have found the end condition, so return a single-value array
		foreach ($data as $index => $item) {
			if (is_null($result) && trim($item['url'], '/') === $url) {
				$result = array( $index );
			}
		}
		// Now check through children of each item, and compose an array using the successful candidate and its index
		foreach ($data as $index => $item) {
			if (is_null($result) && isset($item['children'])) {
				$childrenResult = $this->getNavigationPath($url, $item['children']);
				if (is_array($childrenResult)) {
					$result = array_merge(array( $index ), $childrenResult);
				}
			}
		}
		return $result;
	}

	/**
	 * Retrieve the navigation data item matching the given URL (or equivalent).
	 *
	 * @param string $url
	 * @param array $data
	 *
	 * @return array
	 */
	private function getNavigationItem($url, $data) {
		$result = null;
		// First check if it is at this level, if so we have found the end condition, so return a single-value array
		foreach ($data as $item) {
			if (is_null($result) && trim($item['url'], '/') === $url) {
				$result = $item;
			}
		}
		// Now check through children of each item, and compose an array using the successful candidate and its index
		foreach ($data as $item) {
			if (is_null($result) && isset($item['children'])) {
				$result = $this->getNavigationItem($url, $item['children']);
			}
		}
		return $result;
	}

}
