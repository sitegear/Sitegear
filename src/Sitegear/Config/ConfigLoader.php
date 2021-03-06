<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Config;

use Sitegear\Config\FileLoader\FileLoaderInterface;
use Sitegear\Info\EnvironmentInfoProviderInterface;
use Sitegear\Util\ArrayUtilities;
use Sitegear\Util\TypeUtilities;
use Sitegear\Util\LoggerRegistry;

/**
 * Object for loading configuration data.
 */
class ConfigLoader {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Info\EnvironmentInfoProviderInterface
	 */
	private $environmentInfo;

	/**
	 * @var \Sitegear\Config\FileLoader\FileLoaderInterface[]
	 */
	private $fileLoaders = array();

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Info\EnvironmentInfoProviderInterface $environmentInfo Environment info object.
	 * @param boolean $registerDefaultFileLoaders Whether or not to register the default file loaders, true by default.
	 */
	public function __construct(EnvironmentInfoProviderInterface $environmentInfo, $registerDefaultFileLoaders=true) {
		LoggerRegistry::debug('new ConfigLoader({environment}, {registerDefault})', array( 'environment' => TypeUtilities::describe($environmentInfo), 'registerDefault' => TypeUtilities::describe($registerDefaultFileLoaders) ));
		$this->environmentInfo = $environmentInfo;
		if ($registerDefaultFileLoaders) {
			foreach ($this->defaultFileLoaders() as $fileLoader) {
				$this->registerFileLoader($fileLoader);
			}
		}
	}

	//-- FileLoader Registration Methods --------------------

	/**
	 * Register a file loader of the given class.
	 *
	 * @param string|\Sitegear\Config\FileLoader\FileLoaderInterface $fileLoader Class name of the file loader to
	 *   register, or a class implementing FileLoaderInterface.
	 *
	 * @throws \InvalidArgumentException If the class does not exist or is not a FileLoaderInterface implementation.
	 */
	public function registerFileLoader($fileLoader) {
		LoggerRegistry::debug('ConfigLoader::registerFileLoader({loader})', array( 'loader' => TypeUtilities::describe($fileLoader) ));
		$this->fileLoaders[TypeUtilities::getClassName($fileLoader)] = TypeUtilities::buildTypeCheckedObject(
			$fileLoader,
			'config file loader',
			null,
			'\\Sitegear\\Config\\FileLoader\\FileLoaderInterface'
		);
	}

	/**
	 * Remove any registered file loader of the given class.
	 *
	 * @param string|\Sitegear\Config\FileLoader\FileLoaderInterface $fileLoader Class name of the file loader to
	 *   deregister, or a class implementing FileLoaderInterface.
	 */
	public function deregisterFileLoader($fileLoader) {
		LoggerRegistry::debug('ConfigLoader::deregisterFileLoader({loader})', array( 'loader' => TypeUtilities::describe($fileLoader) ));
		unset($this->fileLoaders[TypeUtilities::getClassName($fileLoader)]);
	}

	/**
	 * Determine whether the given class is registered as a file loader.
	 *
	 * @param string|\Sitegear\Config\FileLoader\FileLoaderInterface $fileLoader Class name of the file loader to
	 *   check for, or a class implementing FileLoaderInterface.
	 *
	 * @return boolean Whether or not the given file loader is registered with the ConfigLoader.
	 */
	public function hasFileLoader($fileLoader) {
		return isset($this->fileLoaders[TypeUtilities::getClassName($fileLoader)]);
	}

	//-- Data Retrieval Methods --------------------

	/**
	 * Loa the data from the given location, according to any available loaders.
	 *
	 * If the given argument is a filename, also load the relevant environment-specific override file.
	 *
	 * @param array|string|\ArrayObject|\Sitegear\Config\Configuration $config Configuration data, filename or
	 *   configuration object.
	 *
	 * @return array Loaded data.
	 *
	 * @throws \InvalidArgumentException If the given argument is not a string or an array.
	 */
	public function load($config) {
		LoggerRegistry::debug('ConfigLoader::load({config})', array( 'config' => TypeUtilities::describe($config) ));
		$result = $this->normalise($config);
		if (is_string($config) && !is_null($this->environmentInfo) && !is_null($this->environmentInfo->getEnvironment())) {
			$pathinfo = pathinfo($config);
			$dirname = array_key_exists('dirname', $pathinfo) ? strval($pathinfo['dirname']) : '';
			$filename = array_key_exists('filename', $pathinfo) ? strval($pathinfo['filename']) : '';
			$extension = array_key_exists('extension', $pathinfo) ? strval($pathinfo['extension']) : '';
			$envFilename = sprintf('%s/%s.%s.%s', $dirname, $filename, $this->environmentInfo->getEnvironment(), $extension);
			$envConfig = $this->loadFile($envFilename);
			$result = ArrayUtilities::combine($result, $envConfig);
		}
		return $result;
	}

	//-- Internal Methods --------------------

	/**
	 * Retrieve the class names of the FileLoaderInterface implementations that are registered by default.
	 *
	 * @return array
	 */
	protected function defaultFileLoaders() {
		return array(
			'\\Sitegear\\Config\\FileLoader\\PhpFileLoader',
			'\\Sitegear\\Config\\FileLoader\\JsonFileLoader'
		);
	}

	/**
	 * Normalise the given configuration into an array, or throw an exception if it cannot be normalised.
	 *
	 * @param array|string|\ArrayObject|\Sitegear\Config\Configuration $config Data to normalise.
	 *
	 * @return array Normalised data.
	 *
	 * @throws \InvalidArgumentException If the argument is of unknown type.
	 */
	private function normalise($config) {
		if (is_string($config)) {
			$config = $this->loadFile($config);
		} elseif ($config instanceof \ArrayObject) {
			$config = $config->getArrayCopy();
		} elseif ($config instanceof Configuration) {
			$config = $config->all();
		} elseif (!is_array($config)) {
			throw new \InvalidArgumentException(sprintf('Unhandled configuration type cannot be normalised [%s]', TypeUtilities::describe($config)));
		}
		return $config;
	}

	/**
	 * Load configuration data from a single file.
	 *
	 * @param $filename
	 *
	 * @return array
	 */
	private function loadFile($filename) {
		$result = null;
		foreach ($this->fileLoaders as $fileLoader) { /** @var \Sitegear\Config\FileLoader\FileLoaderInterface $fileLoader */
			if (is_null($result) && $fileLoader->supports($filename)) {
				$result = $fileLoader->load($filename);
			}
		}
		return $result ?: array();
	}

}
