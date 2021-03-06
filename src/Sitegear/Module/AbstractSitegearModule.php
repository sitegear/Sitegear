<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module;

use Sitegear\Module\AbstractUrlMountableModule;
use Sitegear\Config\Configuration;
use Sitegear\Info\ResourceLocations;
use Sitegear\Util\TypeUtilities;
use Sitegear\View\ViewInterface;
use Sitegear\Module\Doctrine\DoctrineModule;
use Sitegear\Util\LoggerRegistry;

use Sitegear\Util\NameUtilities;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides the baseline implementation for Core and Extension modules.
 *
 * @method \Sitegear\Engine\SitegearEngine getEngine()
 */
abstract class AbstractSitegearModule extends AbstractUrlMountableModule {

	//-- Constants --------------------

	/**
	 * Filename relative to this source file's directory containing default configuration values.
	 */
	const FILENAME_DEFAULTS = 'config/defaults.php';

	/**
	 * Filename relative to this source file's directory containing route settings.
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

	/**
	 * @var boolean
	 */
	private $entitiesConfigured = false;

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function start() {
		LoggerRegistry::debug('{class}::start()', array( 'class' => (new \ReflectionClass($this))->getShortName() ));
	}

	/**
	 * @inheritdoc
	 */
	public function stop() {
		LoggerRegistry::debug('{class}::stop()', array( 'class' => (new \ReflectionClass($this))->getShortName() ));
	}

	/**
	 * @inheritdoc
	 */
	public function getResourceMap() {
		return $this->config('resources', array());
	}

	/**
	 * @inheritdoc
	 */
	public function getContentPath($type, $slug) {
		return sprintf('%s/%s', $this->config(sprintf('paths.%s', $type)), $slug);
	}

	/**
	 * @inheritdoc
	 */
	public function applyViewDefaults(ViewInterface $view, $viewType, $viewName) {
		LoggerRegistry::debug('{class}::applyViewDefaults([view], {viewType}, {viewName})', array( 'class' => (new \ReflectionClass($this))->getShortName(), 'viewType' => TypeUtilities::describe($viewType), 'viewName' => TypeUtilities::describe($viewName) ));
		$this->applyConfigToView('common', $view);
		$this->applyConfigToView(sprintf('%s.%s', NameUtilities::convertToDashedLower($viewType), NameUtilities::convertToDashedLower($viewName)), $view);
	}

	//-- MountableModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function mount($mountedUrl=null, RequestContext $context) {
		parent::mount($mountedUrl, $context);
		$this->baseUrl = '/' . trim($context->getBaseUrl(), '/') . '/';
		$this->urlGenerator = new UrlGenerator($this->getRoutes(), $context);
	}

	/**
	 * @inheritdoc
	 */
	public function getRouteUrl($route, $parameters=null) {
		// Convert non-array parameters to empty array or single-value array with default parameter name.
		if (is_null($parameters)) {
			$parameters = array();
		} elseif (!is_array($parameters)) {
			$parameters = array( $this->getDefaultRouteParameterName() => $parameters );
		}
		// Generate the URL
		$configuredRoute = $this->config(sprintf('routes.%s', $route), $route);
		// TODO Allow configuration between absolute URL, absolute path, network URL, relative path, and path relative to base
		return $this->urlGenerator->generate($configuredRoute, $parameters, UrlGenerator::ABSOLUTE_URL);
	}

	/**
	 * @inheritdoc
	 *
	 * @return string "slug"; can be overridden.
	 */
	public function getDefaultRouteParameterName() {
		return 'slug';
	}

	//-- AbstractConfigurableModule Methods --------------------

	/**
	 * @inheritdoc
	 */
	protected function defaults() {
		return sprintf('%s/%s/%s', $this->getModuleRoot(), ResourceLocations::RESOURCES_DIRECTORY, self::FILENAME_DEFAULTS);
	}

	//-- AbstractUrlMountableModule Methods --------------------

	/**
	 * @inheritdoc
	 */
	protected function buildRoutes() {
		LoggerRegistry::debug('{class}::buildRoutes(), mounted to "{mountedUrl}"', array( 'class' => (new \ReflectionClass($this))->getShortName(), 'mountedUrl' => $this->getMountedUrl() ));
		$routes = new RouteCollection();
		// Check for an index controller and add a route for the module root.
		if ((new \ReflectionObject($this))->hasMethod('indexController')) {
			$routes->add('index', new Route($this->getMountedUrl()));
		}
		// Load routes from file.
		$filename = sprintf('%s/%s/%s', $this->getModuleRoot(), ResourceLocations::RESOURCES_DIRECTORY, self::FILENAME_ROUTES);
		$container = new Configuration($this->getConfigLoader());
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
			$routes->add($name, new Route($path, $defaults, $requirements, $options));
		}
		return $routes;
	}

	/**
	 * @inheritdoc
	 */
	protected function buildNavigationData(/** @noinspection PhpUnusedParameterInspection */ $mode) {
		return array();
	}

	//-- Internal Methods --------------------

	/**
	 * Get the alias used in DQL for entities provided by this module.  By default, the alias is the internal name of
	 * the module.
	 *
	 * @return string
	 */
	protected function getEntityAlias() {
		return $this->getEngine()->getModuleName($this);
	}

	/**
	 * Get the namespace which contains the entities provided by this module.  By default, the namespace is the 'Model'
	 * child namespace of the namespace containing the module implementation class.  If this namespace does not exist,
	 * then the module will not auto-register.
	 *
	 * @return string
	 */
	protected function getEntityNamespace() {
		$class = new \ReflectionClass($this);
		return sprintf('%s\\Model', $class->getNamespaceName());
	}

	/**
	 * @param string $entity
	 *
	 * @return \Doctrine\ORM\EntityRepository
	 */
	protected function getRepository($entity) {
		// TODO Confirm that this does not need to go into start() instead, with a configuration or method override to determine which modules it applies to??
		if (!$this->entitiesConfigured) {
			$this->getEngine()->doctrine()->registerEntityNamespace($this->getEntityAlias(), $this->getEntityNamespace());
			$this->entitiesConfigured = true;
		}
		return $this->getEngine()->doctrine()->getEntityManager()->getRepository(sprintf('%s:%s', $this->getEntityAlias(), $entity));
	}

}
