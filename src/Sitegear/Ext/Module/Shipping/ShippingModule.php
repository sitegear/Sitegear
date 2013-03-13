<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Shipping;

use Sitegear\Base\Module\AbstractConfigurableModule;
use Sitegear\Base\Module\PurchaseAdjustmentProviderModuleInterface;

/**
 * Provides shipping rate calculation and management.
 */
class ShippingModule extends AbstractConfigurableModule implements PurchaseAdjustmentProviderModuleInterface {

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Shipping';
	}

	//-- PurchaseAdjustmentProviderModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function isVisibleUnset() {
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAdjustmentLabel() {
		return $this->config('label');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAdjustmentAmount(array $items, array $data) {
		// TODO Implement me
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isIncludedAmount() {
		return false;
	}
}
