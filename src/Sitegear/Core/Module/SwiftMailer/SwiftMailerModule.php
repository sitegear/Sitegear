<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\SwiftMailer;

use Sitegear\Base\Module\AbstractConfigurableModule;

/**
 * Send mails using the Swiftmailer library and Sitegear's template rendering engine.
 */
class SwiftMailerModule extends AbstractConfigurableModule {

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Mail Processor';
	}

	//-- Public Methods --------------------

	public function send() {
		// TODO
		return true;
	}

}
