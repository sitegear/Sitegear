<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\DiscountCodes;

use Sitegear\Base\Module\PurchaseAdjustmentProviderModuleInterface;
use Sitegear\Core\Module\AbstractCoreModule;

/**
 * Provides a method for accepting discount codes during the checkout process.  Also provides management tools for
 * discount codes so that they can be created, enabled and disabled, and statistics revealed about the usage of codes.
 */
class DiscountCodesModule extends AbstractCoreModule implements PurchaseAdjustmentProviderModuleInterface {

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Discount Codes';
	}

	//-- PurchaseAdjustmentProviderModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function isVisibleUnset() {
		return false;
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
		return 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isIncludedAmount() {
		return false;
	}
}
