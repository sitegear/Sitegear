<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Version;

use Sitegear\Base\View\ViewInterface;
use Sitegear\Module\AbstractCoreModule;
use Sitegear\Util\LoggerRegistry;

/**
 * VersionModule provides version information from the composer.json file.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class VersionModule extends AbstractCoreModule {

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getDisplayName() {
		return 'Sitegear Version Information';
	}

	//-- Component Target Controller Methods --------------------

	/**
	 * Display a link to the Sitegear website.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 */
	public function sitegearLinkComponent(ViewInterface $view) {
		LoggerRegistry::debug('VersionModule::sitegearLinkComponent');
		$view['link-url'] = $this->getEngine()->getSitegearInfo()->getSitegearHomepage();
		$view['link-tooltip'] = sprintf('Running version: %s', $this->getEngine()->getSitegearInfo()->getSitegearVersionIdentifier());
		$view['display-name'] = $this->getEngine()->getSitegearInfo()->getSitegearDisplayName();
	}

}
