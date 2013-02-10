<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\File;

use Sitegear\Base\Module\AbstractConfigurableModule;
use Sitegear\Base\Module\BootstrapModuleInterface;
use Sitegear\Base\Module\DiscreteDataModuleInterface;
use Sitegear\Base\Resources\ResourceLocations;
use Sitegear\Core\Engine\Engine;
use Sitegear\Util\ExtensionMimeTypeGuesser;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * Provides standardised access to the file system.
 *
 * The selectors passed to the data operations (methods defined by DataProviderInterface) are simply file paths
 * relative to the site root.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class FileModule extends AbstractConfigurableModule implements BootstrapModuleInterface, DiscreteDataModuleInterface {

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'File Access';
	}

	//-- BootstrapModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap(Request $request) {
		LoggerRegistry::debug('FileModule running bootstrap');
		// Register the extension-based MIME type guesser which doesn't fail on CSS files.
		MimeTypeGuesser::getInstance()->register(new ExtensionMimeTypeGuesser($this->getEngine()->config('system.mime-types')));
		// TODO files in preview mode
		$filename = $this->getEngine()->getSiteInfo()->getPublicPath(ResourceLocations::RESOURCE_LOCATION_SITE, $this, $request->getPathInfo());
		return $this->getEngine()->createFileResponse($request, $filename);
	}

	//-- DiscreteDataModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function load($selector) {
		LoggerRegistry::debug(sprintf('FileModule loading data from "%s"', $selector));
		$filename = sprintf('%s/%s', $this->getEngine()->getSiteInfo()->getSiteRoot(), ltrim($selector, '/'));
		return file_exists($filename) ? file_get_contents($filename) : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function save($selector, $value) {
		LoggerRegistry::debug(sprintf('FileModule saving data to "%s"', $selector));
		$filename = sprintf('%s/%s', $this->getEngine()->getSiteInfo()->getSiteRoot(), ltrim($selector, '/'));
		mkdir(dirname($filename), 0777, true);
		return file_put_contents($filename, $value) !== false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function upload($selector) {
		LoggerRegistry::debug(sprintf('FileModule uploading data to "%s"', $selector));
		$filename = sprintf('%s/%s', $this->getEngine()->getSiteInfo()->getSiteRoot(), ltrim($selector, '/'));
		mkdir(dirname($filename), 0777, true);
		// TODO Implement me
	}

}
