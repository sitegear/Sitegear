<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Customer;

use Sitegear\Base\Module\AbstractUrlMountableModule;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides customer management functionality.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class CustomerModule extends AbstractUrlMountableModule {

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Customer Experience';
	}

	//-- AbstractUrlMountableModule Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function buildRoutes() {
		// TODO: Implement getRoutes() method.
		return new RouteCollection();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildNavigationData($mode) {
		return array();
	}

}
