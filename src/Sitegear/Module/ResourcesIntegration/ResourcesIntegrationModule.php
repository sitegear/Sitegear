<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\ResourcesIntegration;

use Sitegear\Base\Resources\ResourceLocations;
use Sitegear\Core\Engine\Engine;
use Sitegear\Core\Module\AbstractCoreModule;
use Sitegear\Util\LoggerRegistry;
use Sitegear\Util\NameUtilities;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * This module handles requests for all internal resources (/sitegear/resources/*).
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class ResourcesIntegrationModule extends AbstractCoreModule {

	//-- Constants --------------------

	/**
	 * Special value for the "location" attribute representing the engine scope.
	 */
	const LOCATION_ATTRIBUTE_ENGINE = 'engine';

	/**
	 * Special value for the "location" attribute representing the vendor-resources package scope.
	 */
	const LOCATION_ATTRIBUTE_VENDOR = 'vendor';

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getDisplayName() {
		return 'Site Resources Integration';
	}

	//-- BootstrapModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function resourceController(Request $request) {
		LoggerRegistry::debug('ResourcesIntegrationModule::resourceController');
		$location = $request->attributes->get('location');
		switch ($location) {
			case self::LOCATION_ATTRIBUTE_ENGINE:
				$path = $this->getEngine()->getSiteInfo()->getPublicPath(
					ResourceLocations::RESOURCE_LOCATION_ENGINE,
					$request->attributes->get('path')
				);
				break;
			case self::LOCATION_ATTRIBUTE_VENDOR:
				$path = $this->getEngine()->getSiteInfo()->getPublicPath(
					ResourceLocations::RESOURCE_LOCATION_VENDOR,
					$request->attributes->get('path')
				);
				break;
			default:
				$path = $this->getEngine()->getSiteInfo()->getPublicPath(
					ResourceLocations::RESOURCE_LOCATION_MODULE,
					$request->attributes->get('path'),
					$this->getEngine()->getModule($location)
				);
		}
		if (!file_exists($path)) {
			throw new FileNotFoundException($path);
		}
		return $this->getEngine()->createFileResponse($request, $path);
	}

}
