<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Module;

use Sitegear\Base\Config\Container\SimpleConfigContainer;
use Sitegear\Base\Module\AbstractConfigurableModule;
use Sitegear\Base\Module\MountableModuleInterface;
use Sitegear\Base\Engine\EngineInterface;
use Sitegear\Base\Resources\ResourceLocations;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Extends AbstractConfigurableModule by providing the basic mounting functionality required by MountableModuleInterface.
 * The route and navigation data generation is left to the sub-class.
 */
abstract class AbstractUrlMountableModule extends AbstractConfigurableModule implements MountableModuleInterface {

	//-- Constants --------------------

	const FILENAME_ROUTES = 'config/routes.php';

	//-- Attributes --------------------

	/**
	 * @var string|null
	 */
	private $baseUrl;

	/**
	 * @var string|null
	 */
	private $mountedUrl;

	/**
	 * @var \Symfony\Component\Routing\RouteCollection|null
	 */
	private $routes;

	/**
	 * @var UrlGenerator|null
	 */
	private $generator;

	/**
	 * @var array[]
	 */
	private $navigationData;

	//-- Constructor --------------------

	public function __construct(EngineInterface $engine) {
		parent::__construct($engine);
		$this->baseUrl = null;
		$this->mountedUrl = null;
		$this->routes = null;
		$this->generator = null;
		$this->navigationData = array();
	}

	//-- MountableModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function mount($mountedUrl=null, RequestContext $context) {
		LoggerRegistry::debug(sprintf('%s::mount(%s)', (new \ReflectionClass($this))->getShortName(), $mountedUrl));
		$this->baseUrl = '/' . trim($context->getBaseUrl(), '/') . '/';
		$this->mountedUrl = trim($mountedUrl, '/');
		$this->routes = $this->buildRoutes();
		$this->generator = new UrlGenerator($this->routes, $context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function unmount() {
		LoggerRegistry::debug(sprintf('%s::unmount()', (new \ReflectionClass($this))->getShortName()));
		$this->mountedUrl = null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMountedUrl() {
		return $this->mountedUrl;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRoutes() {
		return $this->routes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRouteUrl($name, array $parameters=null) {
		// TODO Allow configuration between absolute URL, absolute path, network URL, relative path, and path relative to base
		return UrlGenerator::getRelativePath(
			$this->baseUrl,
			$this->generator->generate(
				$this->config(sprintf('routes.%s', $name), $name),
				$parameters ?: array(),
				UrlGenerator::ABSOLUTE_PATH
			)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getNavigationData($mode) {
		if (!isset($this->navigationData[$mode])) {
			$this->navigationData[$mode] = $this->buildNavigationData($mode);
		}
		return $this->navigationData[$mode];
	}

	//-- Internal Methods --------------------

	/**
	 * Build the routes for this module.  This is cached so that this method is only called once per request.
	 *
	 * @return RouteCollection
	 */
	protected function buildRoutes() {
		LoggerRegistry::debug(sprintf('%s::buildRouteCollection(), mounted to "%s"', (new \ReflectionClass($this))->getShortName(), $this->getMountedUrl()));
		$routes = new RouteCollection();
		// Check for an index controller and add a route for the module root.
		if ((new \ReflectionObject($this))->hasMethod('indexController')) {
//			LoggerRegistry::debug('Adding index route');
			$routes->add('index', new Route($this->getMountedUrl()));
		}
		// Load routes from file.
		$filename = sprintf('%s/%s/%s', $this->getModuleRoot(), ResourceLocations::RESOURCES_DIRECTORY, self::FILENAME_ROUTES);
		$container = new SimpleConfigContainer($this->getConfigLoader());
		$container->merge($filename);
		// Add a route for each record in the routes file.
		foreach ($container->all() as $name => $parameters) {
			$defaults = array();
			$requirements = array();
			$options = array();
			$path = sprintf('%s/%s', $this->getMountedUrl(), $this->config(sprintf('routes.%s', $name), $name));
			foreach ($parameters ?: array() as $parameter) {
				$parameterName = $parameter['name'];
				$path = sprintf('%s/{%s}', $path, $parameterName);
				if (isset($parameter['default'])) {
					$defaults[$parameterName] = $parameter['default'];
				}
				if (isset($parameter['requirements'])) {
					$requirements[$parameterName] = $parameter['requirements'];
				}
				if (isset($parameter['options'])) {
					$options[$parameterName] = $parameter['options'];
				}
			}
//			LoggerRegistry::debug(sprintf('Adding route "%s" with path "%s", defaults [ %s ], requirements [ %s ], options [ %s ]', $name, $path, preg_replace('/\\s+/', ' ', print_r($defaults, true)), preg_replace('/\\s+/', ' ', print_r($requirements, true)), preg_replace('/\\s+/', ' ', print_r($options, true))));
			$routes->add($name, new Route($path, $defaults, $requirements, $options));
		}
		return $routes;
	}

	/**
	 * Build the navigation data for this module.  Called once during mount() so that navigation data can be reused.
	 * This method should be overridden by any module wishing to provide navigation data.
	 *
	 * @param integer $mode One of the NAVIGATION_DATA_MODE_* constants.
	 *
	 * @return array
	 */
	protected function buildNavigationData(/** @noinspection PhpUnusedParameterInspection */ $mode) {
		return array();
	}

}
