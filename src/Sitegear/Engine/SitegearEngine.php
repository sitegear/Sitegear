<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Engine;

use Sitegear\Engine\AbstractConfigurableEngine;
use Sitegear\Info\ApplicationInfoProviderInterface;
use Sitegear\User\Storage\UserStorageInterface;
use Sitegear\User\Storage\JsonFileUserStorage;
use Sitegear\View\Factory\ViewFactoryInterface;
use Sitegear\View\StringsManager\StringsManager;
use Sitegear\Info\SitegearApplicationInfoProvider;
use Sitegear\Info\SitegearEnvironmentInfoProvider;
use Sitegear\Info\SitegearSiteInfoProvider;
use Sitegear\User\Manager\SitegearUserManager;
use Sitegear\View\View;
use Sitegear\View\Factory\SitegearViewFactory;
use Sitegear\Util\ExtensionMimeTypeGuesser;
use Sitegear\Util\NameUtilities;
use Sitegear\Util\TypeUtilities;
use Sitegear\Util\FakeMemcache;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Provides a complete concrete implementation of EngineInterface.
 *
 * The following magic methods are defined for known core modules:
 *
 * @method \Sitegear\Module\Content\ContentModule content()
 * @method \Sitegear\Module\Doctrine\DoctrineModule doctrine()
 * @method \Sitegear\Module\File\FileModule file()
 * @method \Sitegear\Module\Forms\FormsModule forms()
 * @method \Sitegear\Module\Navigation\NavigationModule navigation()
 * @method \Sitegear\Module\PageMessages\PageMessagesModule pageMessages()
 * @method \Sitegear\Module\ResourcesIntegration\ResourcesIntegrationModule resourcesIntegration()
 * @method \Sitegear\Module\UserIntegration\UserIntegrationModule userIntegration()
 * @method \Sitegear\Module\Version\VersionModule version()
 *
 * The following magic methods are defined for known extension modules:
 *
 * @method \Sitegear\Module\Customer\CustomerModule customer()
 * @method \Sitegear\Module\DiscountCodes\DiscountCodesModule discountCodes()
 * @method \Sitegear\Module\Eway\EwayModule eway()
 * @method \Sitegear\Module\Google\GoogleModule google()
 * @method \Sitegear\Module\Locations\LocationsModule locations()
 * @method \Sitegear\Module\MailChimp\MailChimpModule mailChimp()
 * @method \Sitegear\Module\News\NewsModule news()
 * @method \Sitegear\Module\Products\ProductsModule products()
 * @method \Sitegear\Module\RealCaptcha\RealCaptchaModule realCaptcha()
 * @method \Sitegear\Module\SalesTax\SalesTaxModule salesTax()
 * @method \Sitegear\Module\Shipping\ShippingModule shipping()
 * @method \Sitegear\Module\SwiftMailer\SwiftMailerModule swiftMailer()
 */
class SitegearEngine extends AbstractConfigurableEngine {

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
	 * @param string|\Sitegear\Info\SiteInfoProviderInterface $siteInfo If a string, is the site
	 *   root directory, which is used to construct a default implementation of SiteInfoProviderInterface.  Otherwise
	 *   must be an implementation of SiteInfoProviderInterface.
	 * @param string|\Sitegear\Info\EnvironmentInfoProviderInterface|null $environment If a string,
	 *   is the environment setting value, which is used to construct a default implementation of
	 *   EnvironmentInfoProviderInterface.  Otherwise must be an implementation of EnvironmentInfoProviderInterface.
	 * @param \Sitegear\Info\ApplicationInfoProviderInterface|null $sitegearInfo Either an
	 *   implementation of ApplicationInfoProviderInterface, or null to use the default implementation.
	 * @param \Sitegear\View\Factory\ViewFactoryInterface|null Either an implementation of ViewFactoryInterface, or
	 *   null to use the default implementation.
	 * @param \Sitegear\User\Storage\UserStorageInterface|null $userStorage
	 */
	public function __construct($siteInfo, $environment, ApplicationInfoProviderInterface $sitegearInfo=null, ViewFactoryInterface $viewFactory=null, UserStorageInterface $userStorage=null) {
		LoggerRegistry::debug('new SitegearEngine()');
		$siteInfo = !is_null($sitegearInfo) && !is_string($siteInfo) ? $siteInfo : new SitegearSiteInfoProvider($this, $siteInfo);
		$environment = !is_null($environment) && !is_string($environment) ? $environment : new SitegearEnvironmentInfoProvider($environment);
		$sitegearInfo = $sitegearInfo ?: new SitegearApplicationInfoProvider($this);
		$viewFactory = $viewFactory ?: new SitegearViewFactory($this);
		$userManager = new SitegearUserManager($userStorage ?: $this->createUserStorage($siteInfo->getSiteRoot(), $environment->getEnvironment()));
		parent::__construct($siteInfo, $environment, $sitegearInfo, $viewFactory, $userManager);
	}

	//-- EngineInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getErrorRoute() {
		$module = NameUtilities::convertToDashedLower($this->config('engine.module-resolution.error-content'));
		return sprintf('%s:error', $module);
	}

	/**
	 * @inheritdoc
	 */
	public function getErrorTemplate() {
		return $this->config('templates.error-template');
	}

	/**
	 * @inheritdoc
	 */
	public function renderPage(Request $request) {
		LoggerRegistry::debug('SitegearEngine::renderPage()');
		return Response::create(
			$this->getViewFactory()->getPage()
					->activateDecorators($this->config('view.page.decorators', array()))
					->pushTarget($this->config('view.page.template-module', array()))
					->pushTarget($request->attributes->get('_template'))
					->render(),
			intval($request->attributes->get('_status') ?: 200)
		);
	}

	/**
	 * @inheritdoc
	 *
	 * This implementation adds a Content-Type header based on configuration (only if the Content-Type is not already
	 * set), and an X-Powered-By header for documentation purposes.
	 */
	public function instrumentResponse(Response $response) {
		LoggerRegistry::debug('SitegearEngine::instrumentResponse()');
		if (!$response->headers->has('Content-Type')) {
			$contentType = $this->config('view.page.content-type');
			if (is_string($contentType) && strlen($contentType) > 0) {
				$response->headers->set('Content-Type', $contentType);
			}
		}
		$response->headers->set('X-Powered-By', $this->getApplicationInfo()->getSitegearVersionIdentifier());
		return $response;
	}

	/**
	 * @inheritdoc
	 */
	public function createFileResponse(Request $request, $filename, $conditional=true) {
		LoggerRegistry::debug('SitegearEngine::createFileResponse([request], {filename}, {conditional})', array( 'filename' => TypeUtilities::describe($filename), 'conditional' => TypeUtilities::describe($conditional) ));
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

	/**
	 * @inheritdoc
	 */
	public function getResourceMap() {
		return $this->config('resources', array());
	}

	/**
	 * @inheritdoc
	 */
	public function normaliseResourceMap(array $resources) {
		LoggerRegistry::debug('SitegearEngine::normaliseResourceMap()');
		// Retrieve and normalise the preferences for CDN vs local delivery.
		$preferCdn = $this->config('system.resources.prefer-cdn');
		if ($preferCdn === true) {
			$preferCdn = array();
		} else {
			$preferCdn = array( 'default' => null );
		}
		$preferCdn = array_merge(array( 'default' => true, 'overrides' => array() ), $preferCdn);
		// Perform processing on each resource entry.
		$environment = $this->getEnvironmentInfo()->getEnvironment();
		array_walk($resources, function(&$resource, $resourceKey) use ($preferCdn, $environment) {
			// If there is a 'cdn-url' key, and the preference for this resource is for CDN delivery, then copy the
			// 'cdn-url' value to the 'url' key, and remove the 'cdn-url' key.
			if (isset($resource['cdn-url'])) {
				if (isset($preferCdn['overrides'][$resourceKey]) ? $preferCdn['overrides'][$resourceKey] : $preferCdn['default']) {
					$resource['url'] = is_array($resource['cdn-url']) ? $resource['cdn-url'] : array( 'default' => $resource['cdn-url'] );
				}
				unset($resource['cdn-url']);
			}
			// Now, if the 'url' key is an array, replace it with either the default or the environment-specific
			// override as appropriate.
			if (is_array($resource['url'])) {
				if (isset($resource['url']['overrides']) && isset($resource['url']['overrides'][$environment])) {
					$resource['url'] = $resource['url']['overrides'][$environment];
				} else {
					$resource['url'] = $resource['url']['default'];
				}
			}
		});
		// Return the modified array.
		return $resources;
	}

	//-- ModuleResolverInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getModuleClassName($name) {
		$name = NameUtilities::convertToStudlyCaps($name);
		$classMap = $this->config('engine.modules.class-map', array());
		if (isset($classMap[$name])) {
			return $classMap[$name];
		} else {
			foreach ($this->config('engine.modules.namespaces', array()) as $namespace) {
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
	 * @inheritdoc
	 */
	public function getModuleName($module) {
		$r = is_string($module) ? new \ReflectionClass($module) : new \ReflectionObject($module);
		if (!$r->implementsInterface('\\Sitegear\\Module\\ModuleInterface')) {
			throw new \InvalidArgumentException('Invalid module specified to determine module name: ' . TypeUtilities::describe($module));
		}
		$suffix = $this->config('engine.modules.class-name-suffix');
		return preg_replace(sprintf('/%s$/', $suffix), '', $r->getShortName());
	}

	/**
	 * @inheritdoc
	 */
	public function getDefaultContentModule() {
		return $this->config('engine.module-resolution.default-content');
	}

	/**
	 * @inheritdoc
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
	 * @inheritdoc
	 */
	protected function initMemcache() {
		$memcache = null;
		if ($this->config('memcache.enabled')) {
			$memcache = new \Memcache();
			foreach ($this->config('memcache.servers') as $server) {
				if (isset($server['host'])) {
					if (isset($server['port'])) {
						$memcache->addServer($server['host'], $server['port']);
					} else {
						$memcache->addServer($server['host']);
					}
				} else {
					throw new \InvalidArgumentException('Engine cannot connect to memcache without specifying at least the host to connect to.');
				}
			}
		}
		return $memcache ?: new FakeMemcache();
	}

	/**
	 * @inheritdoc
	 */
	protected function getRawRouteMap() {
		return array_merge(
			array(
				array(
					'root' => '/',
					'module' => 'content'
				)
			),
			$this->config('routes.map', array()),
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
	 * @inheritdoc
	 */
	protected function getRawTemplateMap() {
		return $this->config('templates.map');
	}

	/**
	 * @inheritdoc
	 */
	protected function getDefaultProtocolScheme() {
		return $this->config('protocols.default');
	}

	/**
	 * @inheritdoc
	 */
	protected function getRawProtocolSchemeMap() {
		return $this->config('protocols.map', array());
	}

	/**
	 * @inheritdoc
	 */
	protected function getRenderers() {
		return $this->config('view.renderers', array());
	}

	/**
	 * @inheritdoc
	 */
	protected function getDecoratorMap() {
		return $this->config('view.decorators', array());
	}

	/**
	 * @inheritdoc
	 */
	protected function getResourceTypeMap() {
		return $this->config('view.resource-types', array());
	}

	//-- AbstractConfigurableEngine Methods --------------------

	/**
	 * @inheritdoc
	 */
	protected function getRootModuleOverrideConfigKey() {
		return self::CONFIG_KEY_MODULE_OVERRIDES;
	}

	//-- Internal Methods --------------------

	protected function createUserStorage($siteRoot, $environment) {
		$userFilename = sprintf('%s/%s', $siteRoot, self::DEFAULT_USER_JSON_FILE);
		$environmentUserFilename = sprintf('%s/%s', $siteRoot, str_replace('%environment%', $environment, self::DEFAULT_ENVIRONMENT_USER_JSON_FILE));
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
	 * @return \Sitegear\Module\ModuleInterface
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
