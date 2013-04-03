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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RequestContext;
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
	 * @var \Memcache
	 */
	private $memcache;

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

	/**
	 * @var string
	 */
	private $currentProtocolScheme;

	/**
	 * @var array[]
	 */
	private $compiledProtocolSchemeMap;

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
		$this->memcache = null;
		$this->modules = array();
		$this->compiledRouteCollection = null;
		$this->compiledTemplateMap = null;
		$this->currentProtocolScheme = null;
		$this->compiledProtocolSchemeMap = null;
	}

	//-- EngineInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 *
	 * This implementation registers the request in the view factory, and sets up the session.
	 */
	public function start(Request $request) {
		// Set the current protocol scheme from the Request.
		$this->currentProtocolScheme = $request->getScheme();
		// Setup view related dependencies.
		$this->getViewFactory()->setRequest($request);
		$this->getViewFactory()->getRendererRegistry()->register($this->getRenderers());
		$this->getViewFactory()->getDecoratorRegistry()->registerMap($this->getDecoratorMap());
		$this->getViewFactory()->getResourcesManager()->registerTypeMap($this->getResourceTypeMap());
		$this->getViewFactory()->getResourcesManager()->registerMap($this->normaliseResourceMap($this->getResourceMap()));
		// Setup memcached connection.
		$this->memcache = $this->initMemcache();
		// Setup session storage and user management.
		$this->session = $this->createSession($request);
		$this->getUserManager()->setSession($this->session);
		// Run the bootstrap sequence.
		$bootstrapResponse = $this->bootstrap($request);
		// If the bootstrap ended normally, create the route collection.
		if (is_null($bootstrapResponse)) {
			$this->compileRouteCollection($request);
		}
		return $bootstrapResponse;
	}

	/**
	 * {@inheritDoc}
	 */
	public function stop() {
		foreach ($this->modules as $module) {
			$module->stop();
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRouteMap() {
		return $this->compiledRouteCollection;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getModuleForUrl($url) {
		$result = null;
		$url = ltrim($url, '/');
		foreach ($this->getRawRouteMap() as $route) {
			if (ltrim($route['root'], '/') === $url) {
				$result = $route['module'];
			}
		}
		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getModuleMountedUrl($module) {
		$result = null;
		foreach ($this->getRawRouteMap() as $route) {
			if ($route['module'] === $module) {
				$result = ltrim($route['root'], '/');
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
						UrlUtilities::compileWildcardUrl(trim($entry['pattern'], '/'));
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
	public function getProtocolSchemeForUrl($url) {
		// Set the default result.
		$result = $this->getDefaultProtocolScheme();
		// Lazy compile the patterns.
		if (is_null($this->compiledProtocolSchemeMap)) {
			// Retrieve raw patterns from subclass.
			$this->compiledProtocolSchemeMap = $this->getRawProtocolSchemeMap();
			// Compile all wildcard patterns into regular expressions.
			foreach ($this->compiledProtocolSchemeMap as $index => $entry) {
				$this->compiledProtocolSchemeMap[$index]['compiled-pattern'] = (isset($entry['regex']) && $entry['regex']) ?
						$entry['pattern'] :
						UrlUtilities::compileWildcardUrl(trim($entry['pattern'], '/'));
			}
		}
		// Now look for the match to the given URL.
		foreach ($this->compiledProtocolSchemeMap as $entry) {
			if (preg_match($entry['compiled-pattern'], $url)) {
				$result = $entry['protocol'];
			}
		}
		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEngineRoot() {
		$obj = new \ReflectionClass($this);
		return dirname($obj->getFileName());
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
	public function getMemcache() {
		return $this->memcache;
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
			$module = $this->createModule($name);
			$module->start();
			$this->getViewFactory()->getResourcesManager()->registerMap($this->normaliseResourceMap($module->getResourceMap()));
			$this->modules[$name] = $module;
		}
		return $this->modules[$name];
	}

	//-- Internal Methods --------------------

	/**
	 * Perform the internal bootstrap sequence
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return null|\Symfony\Component\HttpFoundation\Response
	 */
	protected function bootstrap(Request $request) {
		$response = null;
		// Detect redirection based on protocol.
		$requiredProtocolScheme = $this->getProtocolSchemeForUrl(ltrim($request->getPathInfo(), '/'));
		if (!is_null($requiredProtocolScheme) && $request->getScheme() !== $requiredProtocolScheme) {
			$response = new RedirectResponse(sprintf('%s://%s%s%s', $requiredProtocolScheme, $request->getHttpHost(), $request->getBasePath(), $request->getPathInfo()));
		}
		// Bootstrap module sequence.
		foreach ($this->getBootstrapModuleSequence() as $name) {
			if (is_null($response)) {
				$module = $this->getModule($name); /** @var \Sitegear\Base\Module\BootstrapModuleInterface $module */
				$response = $module->bootstrap($request);
			}
		}
		return $response;
	}

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
			return TypeUtilities::buildTypeCheckedObject(
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
	 * Initialise the session.
	 *
	 * @param Request $request
	 *
	 * @return Session
	 */
	protected function createSession(Request $request) {
		if ($request->hasSession()) {
			$session = $request->getSession();
		} else {
			$session = new Session();
			$request->setSession($session);
		}
		$session->setName('sitegear');
		if (!$session->isStarted()) {
			$session->start();
		}
		return $session;
	}

	/**
	 * Initialise the routing for this Engine.
	 */
	protected function compileRouteCollection(Request $request) {
		// Setup the request context.
		$context = new RequestContext();
		$context->fromRequest($request);
		// Compile the collection.
		$this->compiledRouteCollection = new RouteCollection();
		foreach ($this->getRawRouteMap() as $urlMapEntry) {
			$module = $this->getModule($urlMapEntry['module']); /** @var \Sitegear\Base\Module\MountableModuleInterface $module */
			$module->mount('/' . trim($urlMapEntry['root'], '/'), $context);
			$routes = $module->getRoutes();
			if (!empty($routes)) {
				$routeNamespace = NameUtilities::convertToDashedLower($urlMapEntry['module']);
				foreach ($routes as $routeName => $routeObject) {
					$this->compiledRouteCollection->add(sprintf('%s:%s', $routeNamespace, $routeName), $routeObject);
				}
			}
		}
	}

	/**
	 * Initialise the memcache object (or fake object).
	 */
	protected abstract function initMemcache();

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
	 * Get the protocol preference used by default.
	 *
	 * @return string|null Either 'http' or 'https', or null to indicate no default protocol preference.
	 */
	protected abstract function getDefaultProtocolScheme();

	/**
	 * Get the original (uncompiled) protocol scheme mappings according to implementation.
	 *
	 * @return array[] Array of key-value arrays, where the keys are "pattern", "protocol" and optionally "regex".
	 */
	protected abstract function getRawProtocolSchemeMap();

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
	 * Normalise the raw resource map returned by the `getResourceMap()` in this class and in
	 *
	 * @param array $resources
	 *
	 * @return array
	 */
	protected abstract function normaliseResourceMap(array $resources);

}
