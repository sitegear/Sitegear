<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Engine;

use Sitegear\Config\ConfigurableInterface;
use Sitegear\Config\Processor\IncludeTokenProcessor;
use Sitegear\Config\Processor\EngineTokenProcessor;
use Sitegear\Config\Processor\ConfigTokenProcessor;
use Sitegear\Config\ConfigLoader;
use Sitegear\Config\Configuration;
use Sitegear\Info\ResourceLocations;
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
	 * @var \Sitegear\Config\ConfigLoader Configuration loader, stored for use when constructing modules.
	 */
	private $configLoader;

	/**
	 * @var \Sitegear\Config\Configuration Configuration object.
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
	public function configure($overrides=null) {
		LoggerRegistry::debug('AbstractConfigurableEngine::configure({overrides})', array( 'overrides' => TypeUtilities::describe($overrides) ));
		$this->configLoader = new ConfigLoader($this->getEnvironmentInfo());
		$this->config = new Configuration($this->configLoader);
		$this->config->addProcessor(new EngineTokenProcessor($this, 'engine'));
		$this->config->addProcessor(new ConfigTokenProcessor($this, 'config'));
		$roots = array(
			'site' => $this->getSiteInfo()->getSiteRoot(),
			'sitegear' => $this->getApplicationInfo()->getSitegearRoot(),
			'engine' => $this->getEngineRoot()
		);
		$this->config->addProcessor(new IncludeTokenProcessor($roots, $this->configLoader, 'include'));
		$this->config->merge($this->defaults());
		if (!is_null($overrides)) {
			$this->config->merge($overrides);
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
		LoggerRegistry::debug('AbstractConfigurableEngine::createModule({name})', array( 'name' => TypeUtilities::describe($name) ));
		$module = parent::createModule($name);
		if ($module instanceof ConfigurableInterface) {
			$moduleConfigKey = sprintf('%s.%s', $this->getRootModuleOverrideConfigKey(), NameUtilities::convertToDashedLower($name));
			$moduleConfig = $this->config($moduleConfigKey, array());
			$module->configure($moduleConfig);
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
