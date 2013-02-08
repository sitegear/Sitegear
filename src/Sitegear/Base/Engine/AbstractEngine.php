<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Engine;

use Sitegear\Base\Info\SitegearInfoProviderInterface;
use Sitegear\Base\Info\EnvironmentInfoProviderInterface;
use Sitegear\Base\Info\SiteInfoProviderInterface;
use Sitegear\Base\User\Manager\UserManagerInterface;
use Sitegear\Base\View\Factory\AbstractViewFactory;
use Sitegear\Base\View\Factory\ViewFactoryInterface;
use Sitegear\Util\UrlUtilities;
use Sitegear\Util\NameUtilities;
use Sitegear\Util\TypeUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouteCollection;

/**
 * Default partial implementation of EngineInterface, based on optional constructor dependency injection.
 */
abstract class AbstractEngine implements EngineInterface {

	//-- Attributes --------------------

	/**
	 * @var float Microsecond timestamp.
	 */
	private $timestamp;

	/**
	 * @var \Sitegear\Base\Module\ModuleInterface[] Array mapping names to ModuleInterface implementations.
	 */
	private $modules;

	/**
	 * @var \Sitegear\Base\View\Factory\ViewFactoryInterface
	 */
	private $viewFactory;

	/**
	 * @var \Sitegear\Base\Info\SiteInfoProviderInterface
	 */
	private $siteInfo;

	/**
	 * @var \Sitegear\Base\Info\EnvironmentInfoProviderInterface
	 */
	private $environmentInfo;

	/**
	 * @var \Sitegear\Base\Info\SitegearInfoProviderInterface
	 */
	private $sitegearInfo;

	/**
	 * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
	 */
	private $session;

	/**
	 * @var \Sitegear\Base\User\UserInterface
	 */
	private $loggedInUser;

	/**
	 * @var \Sitegear\Base\User\Manager\UserManagerInterface
	 */
	private $userManager;

	/**
	 * @var \Symfony\Component\Routing\RouteCollection
	 */
	private $compiledRouteCollection;

	/**
	 * @var array[]
	 */
	private $compiledTemplateMap;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\Info\SiteInfoProviderInterface $siteInfo
	 * @param \Sitegear\Base\Info\EnvironmentInfoProviderInterface|string|null $environmentInfo
	 * @param \Sitegear\Base\Info\SitegearInfoProviderInterface $sitegearInfo
	 * @param \Sitegear\Base\View\Factory\ViewFactoryInterface $viewFactory
	 * @param \Sitegear\Base\User\Manager\UserManagerInterface $userManager
	 */
	public function __construct(SiteInfoProviderInterface $siteInfo, EnvironmentInfoProviderInterface $environmentInfo, SitegearInfoProviderInterface $sitegearInfo, ViewFactoryInterface $viewFactory, UserManagerInterface $userManager) {
		LoggerRegistry::debug('Instantiating AbstractEngine');
		$this->timestamp = microtime(true);
		$this->siteInfo = $siteInfo;
		$this->environmentInfo = $environmentInfo;
		$this->sitegearInfo = $sitegearInfo;
		$this->viewFactory = $viewFactory;
		$this->userManager = $userManager;
		$this->session = null;
		$this->loggedInUser = null;
		$this->modules = array();
		$this->compiledRouteCollection = null;
		$this->compiledTemplateMap = null;
	}

	//-- EngineInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 *
	 * This implementation registers the request in the view factory, and sets up the session.
	 */
	public function ignition(Request $request) {
		// Setup view related dependencies.
		$this->getViewFactory()->setRequest($request);
		$this->getViewFactory()->getRendererRegistry()->register($this->getRenderers());
		$this->getViewFactory()->getDecoratorRegistry()->registerMap($this->getDecoratorMap());
		$this->getViewFactory()->getResourcesManager()->registerTypeMap($this->getResourceTypeMap());
		$this->getViewFactory()->getResourcesManager()->registerMap($this->getResourceMap());
		// Setup session storage.
		if ($request->hasSession()) {
			$this->session = $request->getSession();
		} else {
			$this->session = new Session();
			$request->setSession($this->session);
		}
		$this->session->setName('sitegear');
		if (!$this->session->isStarted()) {
			$this->session->start();
		}
		// Get logged in user.
		$this->getUserManager()->setSession($this->session);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRouteMap() {
		if (is_null($this->compiledRouteCollection)) {
			$this->compiledRouteCollection = new RouteCollection();
			foreach ($this->getRawRouteMap() as $urlMapEntry) {
				$module = $this->getModule($urlMapEntry['module']); /** @var \Sitegear\Base\Module\MountableModuleInterface $module */
				$module->mount('/' . trim($urlMapEntry['root'], '/'));
				$routes = $module->getRoutes();
				if (!empty($routes)) {
					$baseRouteName = NameUtilities::convertToDashedLower($urlMapEntry['module']);
					foreach ($routes as $routeName => $routeObject) {
						$this->compiledRouteCollection->add(sprintf('%s:%s', $baseRouteName, $routeName), $routeObject);
					}
				}
			}
		}
		return $this->compiledRouteCollection;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getModuleForUrl($url) {
		$result = null;
		$url = trim($url, '/');
		foreach ($this->getRawRouteMap() as $route) {
			if (trim($route['root'], '/') === $url) {
				$result = $route['module'];
			}
		}
		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplateMap() {
		if (is_null($this->compiledTemplateMap)) {
			// Retrieve raw patterns from subclass.
			$this->compiledTemplateMap = $this->getRawTemplateMap();
			// Compile all wildcard patterns into regular expressions.
			foreach ($this->compiledTemplateMap as $index => $entry) {
				$this->compiledTemplateMap[$index]['compiled-pattern'] = (isset($entry['regex']) && $entry['regex']) ?
						$entry['pattern'] :
						UrlUtilities::compileWildcardUrl($entry['pattern']);
			}
		}
		return $this->compiledTemplateMap;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplateForUrl($url) {
		$result = null;
		$url = trim($url, '/');
		foreach ($this->getRawTemplateMap() as $route) {
			if (strpos($url, trim($route['root'], '/')) === 0) {
				$result = $route['template'];
			}
		}
		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEngineRoot() {
		$obj = new \ReflectionClass($this);
		return dirname($obj->getFileName()) . '/';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSiteInfo() {
		return $this->siteInfo;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEnvironmentInfo() {
		return $this->environmentInfo;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSitegearInfo() {
		return $this->sitegearInfo;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getViewFactory() {
		return $this->viewFactory;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSession() {
		return $this->session;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUserManager() {
		return $this->userManager;
	}

	//-- ModuleContainerInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function hasModule($name) {
		return array_key_exists($name, $this->modules) || class_exists($this->getModuleClassName($name));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getModule($name) {
		$name = NameUtilities::convertToStudlyCaps($name);
		if (!isset($this->modules[$name])) {
			$this->modules[$name] = $this->createModule($name);
			$this->modules[$name]->start();
		}
		return $this->modules[$name];
	}

	//-- Internal Methods --------------------

	/**
	 * Instantiate a module instance for this engine.
	 *
	 * @param string $name Module to load.
	 *
	 * @return \Sitegear\Base\Module\ModuleInterface
	 *
	 * @throws \InvalidArgumentException If the named module does not exist.
	 * @throws \DomainException If the named module does not implement ModuleInterface.
	 */
	protected function createModule($name) {
		LoggerRegistry::debug(sprintf('AbstractEngine creating module "%s"', $name));
		try {
			return TypeUtilities::typeCheckedObject(
				$this->getModuleClassName($name) ?: '',
				'module',
				null,
				'\\Sitegear\\Base\\Module\\ModuleInterface',
				array( $this )
			);
		} catch (\DomainException $e) {
			throw new \InvalidArgumentException(sprintf('AbstractEngine cannot create module "%s" because it does not exist', $name), 0, $e);
		}
	}

	/**
	 * Get the route mappings according to implementation.
	 *
	 * @return array[] Array of key-value arrays, where the keys are "root" and "module".
	 */
	protected abstract function getRawRouteMap();

	/**
	 * Get the original (uncompiled) template mappings according to implementation.
	 *
	 * @return array[] Array of key-value arrays, where the keys are "pattern", "template" and optionally "regex".
	 */
	protected abstract function getRawTemplateMap();

	/**
	 * Get a flat list of class names implementing \Sitegear\Base\View\Renderer\RendererInterface.
	 *
	 * @return string[]
	 */
	protected abstract function getRenderers();

	/**
	 * Get a map of decorator names to class names implementing \Sitegear\Base\View\Decorator\DecoratorInterface.
	 *
	 * @return string[]
	 */
	protected abstract function getDecoratorMap();

	/**
	 * Get a map of resource type names to format strings.
	 *
	 * @return string[]
	 */
	protected abstract function getResourceTypeMap();

	/**
	 * Get a map of resource names to resource descriptor maps.
	 *
	 * @return string[]
	 */
	protected abstract function getResourceMap();

}
