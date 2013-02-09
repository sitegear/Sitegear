<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\MailChimp;

use Sitegear\Base\Module\AbstractConfigurableModule;

class MailChimpModule extends AbstractConfigurableModule {

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'MailChimp Integration';
	}

	//-- Public Methods --------------------

	public function subscribe() {
	}

}
