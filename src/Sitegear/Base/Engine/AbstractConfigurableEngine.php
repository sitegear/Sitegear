<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Engine;

use Sitegear\Base\Config\ConfigurableInterface;
use Sitegear\Base\Config\Processor\IncludeTokenProcessor;
use Sitegear\Base\Config\Processor\EngineTokenProcessor;
use Sitegear\Base\Config\Processor\ConfigTokenProcessor;
use Sitegear\Base\Config\ConfigLoader;
use Sitegear\Base\Config\Container\SimpleConfigContainer;
use Sitegear\Base\Resources\ResourceLocations;
use Sitegear\Util\ExtensionMimeTypeGuesser;
use Sitegear\Util\TypeUtilities;
use Sitegear\Util\NameUtilities;
use Sitegear\Util\LoggerRegistry;

/**
 * Engine implementation which uses configuration to define all behaviour.  The configure() method must be called
 * before the start() method can be called.
 *
 * The expected structure of the configuration file is given with documentation in the defaults.php file.
 */
abstract class AbstractConfigurableEngine extends AbstractEngine implements ConfigurableInterface {

	//-- Constants --------------------

	/**
	 * Default configuration filename relative to this source file's directory.
	 */
	const FILENAME_DEFAULTS = 'config/defaults.php';

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Config\ConfigLoader Configuration loader, stored for use when constructing modules.
	 */
	private $configLoader;

	/**
	 * @var \Sitegear\Base\Config\Container\ConfigContainerInterface Configuration object.
	 */
	private $config;

	//-- ConfigurableInterface Methods --------------------

	/**
	 * @inheritdoc
	 *
	 * The $config argument in this implementation may be either a string, which is an absolute file path to a valid
	 * config file, or an array of such file paths, or null.  Each of these will be merged in order after the defaults.
	 * Note it is not possible to pass a pre-configured array of data.
	 *
	 * This implementation automatically adds a ConfigTokenProcessor and an EngineTokenProcessor.
	 */
	public function configure($config=null, ConfigLoader $loader=null, array $additionalProcessors=null) {
		LoggerRegistry::debug('AbstractConfigurableEngine receiving configuration');
		$this->configLoader = $loader ?: new ConfigLoader($this->getEnvironmentInfo());
		$this->config = new SimpleConfigContainer($this->configLoader);
		$this->config->addProcessor(new EngineTokenProcessor($this, 'engine'));
		$this->config->addProcessor(new ConfigTokenProcessor($this, 'config'));
		$roots = array(
			'site' => $this->getSiteInfo()->getSiteRoot(),
			'sitegear' => $this->getSitegearInfo()->getSitegearRoot(),
			'engine' => $this->getEngineRoot()
		);
		$this->config->addProcessor(new IncludeTokenProcessor($roots, $this->configLoader, 'include'));
		foreach ($additionalProcessors ?: array() as $processor) {
			$this->config->addProcessor($processor);
		}
		$this->config->merge($this->defaults());
		if (!is_null($config)) {
			if (!is_array($config)) {
				$config = array( $config );
			}
			foreach ($config as $configFile) {
				$this->config->merge($configFile);
			}
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function config($key, $default=null) {
		return $this->config->get($key, $default);
	}

	/**
	 * @inheritdoc
	 */
	public function getConfigLoader() {
		return $this->configLoader;
	}

	//-- AbstractEngine Methods --------------------

	/**
	 * @inheritdoc
	 */
	protected function createModule($name) {
		LoggerRegistry::debug(sprintf('AbstractConfigurableEngine creating module "%s"', $name));
		$module = parent::createModule($name);
		if ($module instanceof ConfigurableInterface) {
			$moduleConfigKey = sprintf('%s.%s', $this->getRootModuleOverrideConfigKey(), NameUtilities::convertToDashedLower($name));
			$module->configure($this->config($moduleConfigKey, array()), $this->getConfigLoader());
		}
		return $module;
	}

	//-- Internal Methods --------------------

	/**
	 * Get the default configuration for this engine.
	 *
	 * @return null|string|array Filename, data array, etc.
	 */
	protected function defaults() {
		return sprintf('%s/%s/%s', $this->getEngineRoot(), ResourceLocations::RESOURCES_DIRECTORY, self::FILENAME_DEFAULTS);
	}

	/**
	 * Determine the configuration key used as a root key for module overrides.  Keys immediately below this level
	 * should correspond with module names, and below that level the configuration structure of each respective module.
	 *
	 * @return string Root configuration key, ending without a dot; i.e. "foo.bar" not "foo.bar.".
	 */
	protected abstract function getRootModuleOverrideConfigKey();

}
