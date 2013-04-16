<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module;

use Sitegear\Config\ConfigurableInterface;
use Sitegear\Config\ConfigLoader;
use Sitegear\Config\ConfigContainer;
use Sitegear\Config\Processor\IncludeTokenProcessor;
use Sitegear\Config\Processor\EngineTokenProcessor;
use Sitegear\Config\Processor\ConfigTokenProcessor;
use Sitegear\View\ViewInterface;
use Sitegear\Util\LoggerRegistry;
use Sitegear\Util\TypeUtilities;

use Symfony\Component\HttpFoundation\Request;

/**
 * Extends the AbstractModule class so that it is also configurable.  This is still an abstract module, because it does
 * not contain any specific functionality.
 */
abstract class AbstractConfigurableModule extends AbstractModule implements ConfigurableInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Config\ConfigLoader
	 */
	private $configLoader;

	/**
	 * @var \Sitegear\Config\ConfigContainerInterface
	 */
	private $config;

	//-- ConfigurableInterface Methods --------------------

	/**
	 * @inheritdoc
	 *
	 * The argument in this implementation must be a ConfigContainerInterface implementation.
	 *
	 * This implementation automatically adds a ConfigTokenProcessor and an EngineTokenProcessor.
	 */
	public function configure($config=null, ConfigLoader $loader=null, array $additionalProcessors=null) {
		LoggerRegistry::debug(sprintf('Configuring %s...', (new \ReflectionClass($this))->getShortName()));
		$this->configLoader = $loader ?: new ConfigLoader($this->getEngine()->getEnvironmentInfo());
		$this->config = new ConfigContainer($this->configLoader);
		$this->config->addProcessor(new EngineTokenProcessor($this->getEngine(), 'engine'));
		$this->config->addProcessor(new ConfigTokenProcessor($this, 'config'));
		$engine = $this->getEngine();
		if ($engine instanceof ConfigurableInterface) {
			$this->config->addProcessor(new ConfigTokenProcessor($engine, 'engine-config'));
		}
		$roots = array(
			'site' => $this->getEngine()->getSiteInfo()->getSiteRoot(),
			'sitegear' => $this->getEngine()->getApplicationInfo()->getSitegearRoot(),
			'engine' => $this->getEngine()->getEngineRoot(),
			'module' => $this->getModuleRoot()
		);
		$this->config->addProcessor(new IncludeTokenProcessor($roots, $this->configLoader, 'include'));
		foreach ($additionalProcessors ?: array() as $processor) {
			$this->config->addProcessor($processor);
		}
		$this->config->merge($this->defaults());
		if (!is_null($config)) {
			$this->config->merge($config);
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 *
	 * This implementation stores its configuration in the container object's configuration under the module's base
	 * configuration key.
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

	//-- Internal Methods --------------------

	/**
	 * Get the default configuration for this module.
	 *
	 * @return null|string|array Filename, data array, etc.
	 */
	protected abstract function defaults();

	/**
	 * Apply all children of the given config key from this module, as values stored in the given view.  This is a
	 * handy shortcut for making all the top-level configuration items available to the view without requiring an
	 * additional parent key.
	 *
	 * @param string $configKey
	 * @param \Sitegear\View\ViewInterface $view
	 */
	protected function applyConfigToView($configKey, ViewInterface $view) {
		LoggerRegistry::debug(sprintf('Applying config from key "%s" to view', $configKey));
		if (is_array($config = $this->config($configKey))) {
			foreach ($config as $key => $value) {
				$view[$key] = $value;
			}
		}
	}

}
