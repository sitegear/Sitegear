<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Module;

use Sitegear\Base\Config\ConfigurableInterface;
use Sitegear\Base\Config\Processor\IncludeTokenProcessor;
use Sitegear\Base\Config\Processor\EngineTokenProcessor;
use Sitegear\Base\Config\Processor\ConfigTokenProcessor;
use Sitegear\Base\Config\ConfigLoader;
use Sitegear\Base\Config\Container\SimpleConfigContainer;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Util\TypeUtilities;

use Symfony\Component\HttpFoundation\Request;

/**
 * Extends the AbstractModule class so that it is also configurable.  This is still an abstract module, because it does
 * not contain any specific functionality.
 */
abstract class AbstractConfigurableModule extends AbstractModule implements ConfigurableInterface {

	//-- Constants --------------------

	/**
	 * Default configuration filename relative to this source file's directory.
	 */
	const FILENAME_DEFAULTS = 'config/defaults.php';

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Config\ConfigLoader
	 */
	private $configLoader;

	/**
	 * @var \Sitegear\Base\Config\Container\ConfigContainerInterface
	 */
	private $config;

	//-- ConfigurableInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 *
	 * The argument in this implementation must be a ConfigContainerInterface implementation.
	 *
	 * This implementation automatically adds a ConfigTokenProcessor and an EngineTokenProcessor.
	 */
	public function configure($config=null, ConfigLoader $loader=null, array $additionalProcessors=null) {
		$this->configLoader = $loader ?: new ConfigLoader($this->getEngine()->getEnvironmentInfo());
		$this->config = new SimpleConfigContainer($this->configLoader);
		$this->config->addProcessor(new EngineTokenProcessor($this->getEngine(), 'engine'));
		$this->config->addProcessor(new ConfigTokenProcessor($this, 'config'));
		$engine = $this->getEngine();
		if ($engine instanceof ConfigurableInterface) {
			$this->config->addProcessor(new ConfigTokenProcessor($engine, 'engine-config'));
		}
		$roots = array(
			'site' => $this->getEngine()->getSiteInfo()->getSiteRoot(),
			'sitegear' => $this->getEngine()->getSitegearInfo()->getSitegearRoot(),
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
	 * {@inheritDoc}
	 *
	 * This implementation stores its configuration in the container object's configuration under the module's base
	 * configuration key.
	 */
	public function config($key, $default=null) {
		return $this->config->get($key, $default);
	}

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getResourceMap() {
		return $this->config('resources', array());
	}

	//-- Internal Methods --------------------

	/**
	 * @return \Sitegear\Base\Config\ConfigLoader
	 */
	protected function getConfigLoader() {
		return $this->configLoader;
	}

	/**
	 * Get the default configuration for this module.
	 *
	 * @return null|string|array Filename, data array, etc.
	 */
	protected function defaults() {
		return $this->getModuleRoot() . self::FILENAME_DEFAULTS;
	}

	/**
	 * Apply all children of the given config key from this module, as values stored in the given view.  This is a
	 * handy shortcut for making all the top-level configuration items available to the view without requiring an
	 * additional parent key.
	 *
	 * @param string $configKey
	 * @param \Sitegear\Base\View\ViewInterface $view
	 */
	protected function applyConfigToView($configKey, ViewInterface $view) {
		if (is_array($config = $this->config($configKey))) {
			foreach ($config as $key => $value) {
				$view[$key] = $value;
			}
		}
	}

}
