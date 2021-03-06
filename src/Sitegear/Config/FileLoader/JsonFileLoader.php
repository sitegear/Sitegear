<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Config\FileLoader;

use Sitegear\Util\LoggerRegistry;
use Sitegear\Util\TypeUtilities;

/**
 * LoaderInterface implementation which loads configuration data from JSON encoded files.
 *
 * The $args value in this implementation must be a string which is the absolute file path of the JSON file.
 */
class JsonFileLoader implements FileLoaderInterface {

	/**
	 * @inheritdoc
	 */
	public function supports($args) {
		return file_exists($args) && pathinfo($args, PATHINFO_EXTENSION) === 'json';
	}

	/**
	 * @inheritdoc
	 */
	public function load($args) {
		LoggerRegistry::debug('JsonFileLoader::load({args})', array( 'args' => TypeUtilities::describe($args) ));
		if (!$this->supports($args)) {
			throw new \InvalidArgumentException(sprintf('JsonFileLoader attempting to load unsupported config file "%s".', $args));
		}
		return json_decode(file_get_contents($args), true);
	}

}
