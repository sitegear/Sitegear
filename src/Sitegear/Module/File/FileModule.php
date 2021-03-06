<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\File;

use Sitegear\Module\BootstrapModuleInterface;
use Sitegear\Module\DiscreteDataModuleInterface;
use Sitegear\Info\ResourceLocations;
use Sitegear\Engine\SitegearEngine;
use Sitegear\Module\AbstractSitegearModule;
use Sitegear\Util\ExtensionMimeTypeGuesser;
use Sitegear\Util\LoggerRegistry;

use Sitegear\Util\TypeUtilities;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * Provides standardised access to the file system.
 *
 * The selectors passed to the data operations (methods defined by DataProviderInterface) are simply file paths
 * relative to the site root.
 *
 * @method \Sitegear\Engine\SitegearEngine getEngine()
 */
class FileModule extends AbstractSitegearModule implements BootstrapModuleInterface, DiscreteDataModuleInterface {

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getDisplayName() {
		return 'File Access';
	}

	//-- BootstrapModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function bootstrap(Request $request) {
		LoggerRegistry::debug('FileModule::bootstrap()');
		// Register the extension-based MIME type guesser which doesn't fail on CSS files.
		MimeTypeGuesser::getInstance()->register(new ExtensionMimeTypeGuesser($this->getEngine()->config('system.mime-types')));
		// TODO files in preview mode
		$filename = $this->getEngine()->getSiteInfo()->getPublicPath(ResourceLocations::RESOURCE_LOCATION_SITE, $request->getPathInfo(), $this);
		return $this->getEngine()->createFileResponse($request, $filename);
	}

	//-- DiscreteDataModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function load($selector) {
		LoggerRegistry::debug('FileModule::load({selector})', array( 'selector' => TypeUtilities::describe($selector) ));
		$filename = sprintf('%s/%s', $this->getEngine()->getSiteInfo()->getSiteRoot(), ltrim($selector, '/'));
		return file_exists($filename) ? file_get_contents($filename) : null;
	}

	/**
	 * @inheritdoc
	 */
	public function save($selector, $value) {
		LoggerRegistry::debug('FileModule::save({selector}, {value})', array( 'selector' => TypeUtilities::describe($selector), 'value' => TypeUtilities::describe($value) ));
		$filename = sprintf('%s/%s', $this->getEngine()->getSiteInfo()->getSiteRoot(), ltrim($selector, '/'));
		mkdir(dirname($filename), 0777, true);
		return file_put_contents($filename, $value) !== false;
	}

	/**
	 * @inheritdoc
	 */
	public function upload($selector) {
		LoggerRegistry::debug('FileModule::upload({selector})', array( 'selector' => TypeUtilities::describe($selector) ));
		$filename = sprintf('%s/%s', $this->getEngine()->getSiteInfo()->getSiteRoot(), ltrim($selector, '/'));
		mkdir(dirname($filename), 0777, true);
		// TODO Implement me
	}

}
