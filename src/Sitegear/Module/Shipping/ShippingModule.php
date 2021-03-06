<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Shipping;

use Sitegear\Module\PurchaseAdjustmentProviderModuleInterface;
use Sitegear\Module\AbstractSitegearModule;

/**
 * Provides shipping rate calculation and management.
 */
class ShippingModule extends AbstractSitegearModule implements PurchaseAdjustmentProviderModuleInterface {

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getDisplayName() {
		return 'Shipping';
	}

	//-- PurchaseAdjustmentProviderModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function isVisibleUnset() {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function getAdjustmentLabel() {
		return $this->config('label');
	}

	/**
	 * @inheritdoc
	 */
	public function getAdjustmentAmount(array $items, array $data) {
		// TODO Implement me
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function isIncludedAmount() {
		return false;
	}
}
