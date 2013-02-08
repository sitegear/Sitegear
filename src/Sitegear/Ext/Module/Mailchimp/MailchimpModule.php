<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Mailchimp;

use Sitegear\Base\Module\AbstractConfigurableModule;

class MailchimpModule extends AbstractConfigurableModule {

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Mailchimp Integration';
	}

	//-- Public Methods --------------------

	public function subscribe() {
		// TODO
		return true;
	}

}
