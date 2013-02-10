<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\Content;

use Sitegear\Base\Module\AbstractUrlMountableModule;
use Sitegear\Base\Resources\ResourceLocations;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

/**
 * Serves as a default / fallback implementation of several module interfaces that are required for all engine cycles.
 *
 * Specifically, it provides routes and navigation data for the MountableModuleInterface based on the internal site
 * structure and a JSON data file.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class ContentModule extends AbstractUrlMountableModule {

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Main Site Content';
	}

	//-- Page Controller Methods --------------------

	/**
	 * Default page controller, it just renders the page with no custom view data.
	 */
	public function defaultController() {
		LoggerRegistry::debug('ContentModule::defaultController');
		// Do nothing
	}

	//-- Component Target Controller Methods --------------------

	/**
	 * Doctype declaration and optional XML declaration.
	 */
	public function doctypeComponent(ViewInterface $view, $html=null, $xml=false) {
		LoggerRegistry::debug('ContentModule::doctypeComponent');
		$xml = $xml ?: $this->config('view.page.doctype.xml-spec');
		$view['xml-spec'] = $this->getEngine()->config('view.page.doctype.display-xml-spec') ? $this->xmlSpec($xml) : '';
		$html = $html ?: $this->config('view.page.doctype.html-spec');
		$view['doctype-spec'] = $this->getEngine()->config('view.page.doctype.display-doctype') ? $this->doctypeSpec($html) : '';
	}

	/**
	 * Render a string containing class names that can be assigned to a high-level page element (e.g. <html> or <body>)
	 * to identify sections and pages to CSS rules based on URL.  The home page is given the special class given by
	 * the $indexName argument.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param null|int $maxElements Maximum number of path levels to include in the result, starting from the start.
	 *   For example if the relative URL is "/foo/bar/baz" then by default the page this method would return
	 *   "foo foo-bar foo-bar-baz", but if $maxDepth is 2, then it will return "foo foo-bar".
	 */
	public function bodyClassesComponent(ViewInterface $view, Request $request, $maxElements=null) {
		LoggerRegistry::debug('ContentModule::bodyClassesComponent');
		$url = ltrim($request->getPathInfo(), '/');
		$path = !empty($url) ? explode('/', $url) : array( $this->config('body-classes.index') );
		$maxElements = $maxElements ?: sizeof($path);
		$classNames = array();
		for ($i=0; $i<$maxElements; $i++) {
			$classNames[] = implode('-', array_slice($path, 0, $i+1));
		}
		$view['class-names'] = $classNames;
	}

	/**
	 * Render the <base> element and some useful <link> elements.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function baseLinksComponent(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('ContentModule::baseLinksComponent');
		$view['base-url'] = $request->getUriForPath('/');
		$view['canonical-url'] = $request->getUri(); /* TODO Canonicalise me */;
		$view['favicon-url'] = $request->getUriForPath('/favicon.ico');
	}

	//-- AbstractUrlMountableModule Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function buildRoutes() {
		$routes = new RouteCollection();
		$mainSectionPath = $this->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_SITE, $this, 'sections/main/');
		$this->buildRoutesFromMainSectionViewScripts($routes, $mainSectionPath);
		return $routes;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildNavigationData($mode) {
		$path = $this->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_SITE, $this, 'navigation.json');
		return file_exists($path) ? json_decode(file_get_contents($path), true) : array();
	}

	//-- Internal Methods --------------------

	/**
	 * Recursive function to generate Routes based on directory structure.
	 *
	 * @param RouteCollection $collection
	 * @param string $mainSectionPath
	 * @param string $path
	 *
	 * @return array
	 */
	private function buildRoutesFromMainSectionViewScripts(RouteCollection $collection, $mainSectionPath, $path='') {
		foreach (scandir($mainSectionPath . $path) as $entry) {
			if ($entry[0] !== '.') {
				if (is_dir($mainSectionPath . $path . $entry)) {
					$dirPath = $path . $entry . '/';
					$this->buildRoutesFromMainSectionViewScripts($collection, $mainSectionPath, $dirPath);
				} else {
					$name = preg_replace('/\..*$/', '', $entry);
					// Skip error messages TODO document this
					if (!is_numeric($name)) {
						$route = sprintf('%s%s', $path, $name);
						$pattern = $this->getMountedUrl() . ($name === 'index' ? rtrim($path, '/') : $route);
						$collection->add($route, new Route($pattern));
						LoggerRegistry::debug(sprintf('ContentModule generated route "%s" with pattern "%s"', $route, $pattern));
					}
				}
			}
		}
	}

	/**
	 * Retrieve the specified HTML doctype identifier string.
	 *
	 * @param string|null $spec Name of the spec to retrieve.  Case insensitive.  Unknown specs will default to HTML5.
	 *
	 * @return string Document type declaration tag.
	 */
	private function doctypeSpec($spec=null) {
		switch (strtolower($spec)) {
			case 'html4':
			case 'html4-strict':
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">' . PHP_EOL;
			case 'html4-transitional':
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">' . PHP_EOL;
			case 'html4-frameset':
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">' . PHP_EOL;
			case 'xhtml':
			case 'xhtml1':
			case 'xhtml1-strict':
				return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . PHP_EOL;
			case 'xhtml1-transitional':
				return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . PHP_EOL;
			case 'xhtml1-frameset':
				return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">' . PHP_EOL;
			case 'xhtml11':
				return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' . PHP_EOL;
			case 'html':
			case 'html5':
			default:
				return '<!DOCTYPE html>' . PHP_EOL;
		}
	}

	/**
	 * Retrieve the XML declaration.  Should only be used when the Content-Type header has been set to application/xml
	 * or application/xhtml+xml.  By default, this method returns an empty string.
	 *
	 * @param boolean $xml True returns an XML declaration tag, false returns an empty string.
	 *
	 * @return string
	 */
	private function xmlSpec($xml=false) {
		return $xml ? '<?xml version="1.0" encoding="utf-8" ?>' . PHP_EOL : '';
	}

}
