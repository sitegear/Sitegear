<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module;

use Sitegear\Base\Config\Container\SimpleConfigContainer;
use Sitegear\Base\Engine\EngineInterface;
use Sitegear\Base\Module\AbstractUrlMountableModule;
use Sitegear\Base\Resources\ResourceLocations;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides the baseline implementation for Core and Extension modules.
 */
abstract class AbstractCoreModule extends AbstractUrlMountableModule {

	//-- Constants --------------------

	/**
	 * Default configuration filename relative to this source file's directory.
	 */
	const FILENAME_DEFAULTS = 'config/defaults.php';

	/**
	 * Default route configuration filename relative to this source file's directory.
	 */
	const FILENAME_ROUTES = 'config/routes.php';

	//-- Attributes --------------------

	/**
	 * @var string|null
	 */
	private $baseUrl;

	/**
	 * @var UrlGenerator|null
	 */
	private $urlGenerator;

	//-- Constructor --------------------

	public function __construct(EngineInterface $engine) {
		parent::__construct($engine);
		$this->baseUrl = null;
		$this->urlGenerator = null;
	}

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getResourceMap() {
		return $this->config('resources', array());
	}

	/**
	 * {@inheritDoc}
	 */
	public function getContentPath($type, $slug) {
		return sprintf('%s/%s', $this->config(sprintf('paths.%s', $type)), $slug);
	}

	/**
	 * {@inheritDoc}
	 */
	public function applyViewDefaults(ViewInterface $view, $viewType, $viewName) {
		parent::applyViewDefaults($view, $viewType, $viewName);
		$this->applyConfigToView('common', $view);
		$this->applyConfigToView(sprintf('%s.%s', $viewType, $viewName), $view);
	}

	//-- MountableModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function mount($mountedUrl=null, RequestContext $context) {
		parent::mount($mountedUrl, $context);
		$this->baseUrl = '/' . trim($context->getBaseUrl(), '/') . '/';
		$this->urlGenerator = new UrlGenerator($this->getRoutes(), $context);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRouteUrl($route, $parameters=null) {
		// Convert non-array parameters to empty array or single-value array with default parameter name.
		if (is_null($parameters)) {
			$parameters = array();
		} elseif (!is_array($parameters)) {
			$parameters = array( $this->getDefaultRouteParameterName() => $parameters );
		}
		// Generate the URL
		// TODO Allow configuration between absolute URL, absolute path, network URL, relative path, and path relative to base
		$configuredRoute = $this->config(sprintf('routes.%s', $route), $route);
		return UrlGenerator::getRelativePath(
			$this->baseUrl,
			$this->urlGenerator->generate($configuredRoute, $parameters, UrlGenerator::ABSOLUTE_PATH)
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return string "slug"; can be overridden.
	 */
	public function getDefaultRouteParameterName() {
		return 'slug';
	}

	//-- AbstractConfigurableModule Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function defaults() {
		return sprintf('%s/%s/%s', $this->getModuleRoot(), ResourceLocations::RESOURCES_DIRECTORY, self::FILENAME_DEFAULTS);
	}

	//-- AbstractUrlMountableModule Methods --------------------

	/**
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	protected function buildNavigationData(/** @noinspection PhpUnusedParameterInspection */ $mode) {
		return array();
	}

}
