<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\DiscountCodes;

use Sitegear\Module\PurchaseAdjustmentProviderModuleInterface;
use Sitegear\Module\AbstractSitegearModule;

/**
 * Provides a method for accepting discount codes during the checkout process.  Also provides management tools for
 * discount codes so that they can be created, enabled and disabled, and statistics revealed about the usage of codes.
 */
class DiscountCodesModule extends AbstractSitegearModule implements PurchaseAdjustmentProviderModuleInterface {

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getDisplayName() {
		return 'Discount Codes';
	}

	//-- PurchaseAdjustmentProviderModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function isVisibleUnset() {
		return false;
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
		return 0;
	}

	/**
	 * @inheritdoc
	 */
	public function isIncludedAmount() {
		return false;
	}
}
