<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Engine;

use Sitegear\Base\Engine\AbstractConfigurableEngine;
use Sitegear\Base\Info\SitegearInfoProviderInterface;
use Sitegear\Base\User\Storage\UserStorageInterface;
use Sitegear\Base\User\Storage\JsonFileUserStorage;
use Sitegear\Base\View\Factory\ViewFactoryInterface;
use Sitegear\Base\View\Strings\SimpleStringsManager;
use Sitegear\Core\Info\SitegearInfoProvider;
use Sitegear\Core\Info\EnvironmentInfoProvider;
use Sitegear\Core\Info\SiteInfoProvider;
use Sitegear\Core\User\SessionUserManager;
use Sitegear\Core\View\View;
use Sitegear\Core\View\ViewFactory;
use Sitegear\Util\ExtensionMimeTypeGuesser;
use Sitegear\Util\NameUtilities;
use Sitegear\Util\TypeUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Provides a complete implementation of EngineInterface, useful within the context of the Core Sitegear
 * implementation.
 *
 * The following magic methods are defined for known core modules:
 *
 * @method \Sitegear\Core\Module\Content\ContentModule content()
 * @method \Sitegear\Core\Module\Doctrine\DoctrineModule doctrine()
 * @method \Sitegear\Core\Module\File\FileModule file()
 * @method \Sitegear\Core\Module\Navigation\NavigationModule navigation()
 * @method \Sitegear\Core\Module\ResourcesIntegration\ResourcesIntegrationModule resourcesIntegration()
 * @method \Sitegear\Core\Module\UserIntegration\UserIntegrationModule userIntegration()
 * @method \Sitegear\Core\Module\Version\VersionModule version()
 *
 * The following magic methods are defined for known extension modules:
 *
 * @method \Sitegear\Ext\Module\Customer\CustomerModule customer()
 * @method \Sitegear\Ext\Module\Forms\FormsModule forms()
 * @method \Sitegear\Ext\Module\Google\GoogleModule google()
 * @method \Sitegear\Ext\Module\News\NewsModule news()
 * @method \Sitegear\Ext\Module\Products\ProductsModule products()
 */
class Engine extends AbstractConfigurableEngine {

	//-- Constants --------------------

	/**
	 * The base configuration key for all module-specific configuration overrides.  The children of this key should be
	 * module names, with the data structured in a module-specific way beneath each module node.
	 */
	const CONFIG_KEY_MODULE_OVERRIDES = 'modules';

	/**
	 * The filename to use when no other user data provider is specified.
	 */
	const DEFAULT_USER_JSON_FILE = 'config/users.json';

	/**
	 * The filename to use when no other user data provider is specified, environment-specific.
	 */
	const DEFAULT_ENVIRONMENT_USER_JSON_FILE = 'config/users.%environment%.json';

	//-- Constructor --------------------

	/**
	 * @param string|\Sitegear\Base\Info\SiteInfoProviderInterface $siteInfo If a string, is the site
	 *   root directory, which is used to construct a default implementation of SiteInfoProviderInterface.  Otherwise
	 *   must be an implementation of SiteInfoProviderInterface.
	 * @param string|\Sitegear\Base\Info\EnvironmentInfoProviderInterface|null $environment If a string,
	 *   is the environment setting value, which is used to construct a default implementation of
	 *   EnvironmentInfoProviderInterface.  Otherwise must be an implementation of EnvironmentInfoProviderInterface.
	 * @param \Sitegear\Base\Info\SitegearInfoProviderInterface|null $sitegearInfo Either an
	 *   implementation of SitegearInfoProviderInterface, or null to use the default implementation.
	 * @param \Sitegear\Base\View\Factory\ViewFactoryInterface|null Either an implementation of ViewFactoryInterface, or
	 *   null to use the default implementation.
	 * @param \Sitegear\Base\User\Storage\UserStorageInterface|null $userStorage
	 */
	public function __construct($siteInfo, $environment, SitegearInfoProviderInterface $sitegearInfo=null, ViewFactoryInterface $viewFactory=null, UserStorageInterface $userStorage=null) {
		LoggerRegistry::debug('Instantiating Engine');
		$siteInfo = !is_null($sitegearInfo) && !is_string($siteInfo) ? $siteInfo : new SiteInfoProvider($this, $siteInfo);
		$environment = !is_null($environment) && !is_string($environment) ? $environment : new EnvironmentInfoProvider($environment);
		$sitegearInfo = $sitegearInfo ?: new SitegearInfoProvider($this);
		$viewFactory = $viewFactory ?: new ViewFactory($this);
		$userManager = new SessionUserManager($userStorage ?: $this->createUserStorage($siteInfo->getSiteRoot(), $environment->getEnvironment()));
		parent::__construct($siteInfo, $environment, $sitegearInfo, $viewFactory, $userManager);
	}

	//-- EngineInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getErrorRoute() {
		$module = NameUtilities::convertToDashedLower($this->config('engine.module-resolution.error-content'));
		return sprintf('%s:error', $module);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorTemplate() {
		return $this->config('templates.error-template');
	}

	/**
	 * {@inheritDoc}
	 */
	public function renderPage(Request $request) {
		return Response::create(
			$this->getViewFactory()->getPage()
					->applyDecorators($this->config('view.page.decorators', array()))
					->pushTarget($this->config('view.page.template-module', array()))
					->pushTarget($request->attributes->get('_template'))
					->render(),
			intval($request->attributes->get('_status') ?: 200)
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * This implementation adds a Content-Type header based on configuration (only if the Content-Type is not already
	 * set), and an X-Powered-By header for documentation purposes.
	 */
	public function instrumentResponse(Response $response) {
		LoggerRegistry::debug('Engine instrumenting response');
//		if (!$response->headers->has('Content-Type')) {
//			$contentType = $this->config('view.page.content-type');
//			if (is_string($contentType) && strlen($contentType) > 0) {
//				$response->headers->set('Content-Type', $contentType);
//			}
//		}
//		$response->headers->set('X-Powered-By', $this->getSitegearInfo()->getSitegearVersionIdentifier());
		return $response;
	}

	/**
	 * {@inheritDoc}
	 */
	public function createFileResponse(Request $request, $filename, $conditional=true) {
		$response = null;
		if (!$conditional || is_file($filename)) {
			// Determine whether to use the header method.
			$useHeader = $this->config('system.file-response.use-header');
			$header = $this->config('system.file-response.header');
			if ($useHeader === 'detect' && is_string($header) && strlen($header) > 0) {
				$detectFunction = $this->config('system.file-response.detect-function');
				if (function_exists($detectFunction)) {
					$detectValue = $this->config('system.file-response.detect-value');
					$useHeader = in_array($detectValue, call_user_func($detectFunction));
				}
			}
			// Apply header if determined that is the method being used.
			if ($useHeader === true) {
				$request->headers->set('X-Sendfile-Type', $header);
			}
			// Create the response object.
			$response = new BinaryFileResponse($filename);
		}
		return $response;
	}

	//-- ModuleResolverInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getModuleClassName($name) {
		$name = NameUtilities::convertToStudlyCaps($name);
		$classMap = $this->config('engine.modules.class-map', array());
		if (isset($classMap[$name])) {
			return $classMap[$name];
		} else {
			foreach ($this->config('engine.modules.namespaces') as $namespace) {
				$prefix = $this->config('engine.modules.class-name-prefix');
				$suffix = $this->config('engine.modules.class-name-suffix');
				$className = sprintf('%s\\%s\\%s%s%s', $namespace, $name, $prefix, $name, $suffix);
				if (class_exists($className)) {
					return $className;
				}
			}
		}
		return null;
	}

	/**
	 * {@inheritDoc}
	 *
	 * TODO Put this into TypeUtilities similar to above.
	 */
	public function getModuleName($module) {
		$r = is_string($module) ? new \ReflectionClass($module) : new \ReflectionObject($module);
		if (!$r->implementsInterface('\\Sitegear\\Base\\Module\\ModuleInterface')) {
			throw new \InvalidArgumentException('Invalid module specified to determine module name: ' . TypeUtilities::describe($module));
		}
		$suffix = $this->config('engine.modules.class-name-suffix');
		return preg_replace(sprintf('/%s$/', $suffix), '', $r->getShortName());
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefaultContentModule() {
		return $this->config('engine.module-resolution.default-content');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBootstrapModuleSequence() {
		$result = array();
		foreach ($this->config('engine.module-resolution.bootstrap-sequence', array()) as $entry) {
			$result[] = is_array($entry) ? $entry['module'] : $entry;
		}
		return $result;
	}

	//-- AbstractEngine Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function getRawTemplateMap() {
		return $this->config('templates.map');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getRawRouteMap() {
		return array_merge(
			array(
				array(
					'root' => '/',
					'module' => 'content'
				)
			),
			$this->config('routes.map'),
			array(
				array(
					'root' => sprintf('/%s/%s', $this->config('system.command-url.root'), $this->config('system.command-url.user')),
					'module' => 'user-integration'
				)
			),
			array(
				array(
					'root' => sprintf('/%s/%s', $this->config('system.command-url.root'), $this->config('system.command-url.resources')),
					'module' => 'resources-integration'
				)
			)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getRenderers() {
		return $this->config('view.renderers', array());
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getDecoratorMap() {
		return $this->config('view.decorators', array());
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getResourceTypeMap() {
		return $this->config('view.resource-types', array());
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getResourceMap() {
		return $this->config('resources', array());
	}

	//-- AbstractConfigurableEngine Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function getRootModuleOverrideConfigKey() {
		return self::CONFIG_KEY_MODULE_OVERRIDES;
	}

	//-- Internal Methods --------------------

	protected function createUserStorage($siteRoot, $environment) {
		$userFilename = $siteRoot . self::DEFAULT_USER_JSON_FILE;
		$environmentUserFilename = $siteRoot . str_replace('%environment%', $environment, self::DEFAULT_ENVIRONMENT_USER_JSON_FILE);
		return new JsonFileUserStorage(is_file($environmentUserFilename) ? $environmentUserFilename : $userFilename);
	}

	//-- Magic Methods --------------------

	/**
	 * Modules may be accessed by a method named after the module, in camelCase.  For example the method call
	 * "$engine->somethingUseful()" will return an instance of SomethingUsefulModule.  This is exactly the same as
	 * calling "$engine->module('SomethingUseful')" and will emit the same error if the named module does not exist.
	 *
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return \Sitegear\Base\Module\ModuleInterface
	 *
	 * @throw \InvalidArgumentException If the module does not exist.
	 * @throw \DomainException If the module cannot be created.
	 * @throws \BadMethodCallException
	 */
	public function __call($name, array $arguments) {
		if (strlen($name) > 0) {
			return $this->getModule($name);
		} else {
			throw new \BadMethodCallException('Engine cannot retrieve a module using magic method without specifying a name.  This normally indicates a misconfiguration or dependency problem.');
		}
	}

}
