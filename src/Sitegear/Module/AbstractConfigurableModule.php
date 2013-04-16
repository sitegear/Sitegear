<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module;

use Sitegear\Config\ConfigLoader;
use Sitegear\Config\ConfigurableInterface;
use Sitegear\Config\Configuration;
use Sitegear\Config\Processor\IncludeTokenProcessor;
use Sitegear\Config\Processor\EngineTokenProcessor;
use Sitegear\Config\Processor\ConfigTokenProcessor;
use Sitegear\Engine\AbstractConfigurableEngine;
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
	 * @var \Sitegear\Config\Configuration
	 */
	private $config;

	//-- ConfigurableInterface Methods --------------------

	/**
	 * @inheritdoc
	 *
	 * The argument in this implementation must be a Configuration object.
	 *
	 * This implementation automatically adds a ConfigTokenProcessor and an EngineTokenProcessor.
	 */
	public function configure($overrides=null) {
		LoggerRegistry::debug('AbstractConfigurableModule::configure({overrides})', array( 'overrides' => TypeUtilities::describe($overrides) ));
		$this->config = new Configuration($this->getConfigLoader());
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
		$this->config->addProcessor(new IncludeTokenProcessor($roots, $this->getConfigLoader(), 'include'));
		$this->config->merge($this->defaults());
		if (!is_null($overrides)) {
			$this->config->merge($overrides);
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
		if (is_null($this->configLoader)) {
			$engine = $this->getEngine();
			$this->configLoader = $engine instanceof ConfigurableInterface ?
					$engine->getConfigLoader() :
					new ConfigLoader($engine->getEnvironmentInfo());
		}
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
		LoggerRegistry::debug('AbstractConfigurableModule::applyConfigToView({configKey}, [view])', array( 'configKey' => TypeUtilities::describe($configKey) ));
		if (is_array($config = $this->config($configKey))) {
			foreach ($config as $key => $value) {
				$view[$key] = $value;
			}
		}
	}

}
