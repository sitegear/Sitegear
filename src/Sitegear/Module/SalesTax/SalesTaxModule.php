<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\SalesTax;

use Sitegear\Module\PurchaseAdjustmentProviderModuleInterface;
use Sitegear\Module\AbstractSitegearModule;
use Sitegear\Util\StringUtilities;

/**
 * Provides sales tax calculation and management tools for sales tax.
 */
class SalesTaxModule extends AbstractSitegearModule implements PurchaseAdjustmentProviderModuleInterface {

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getDisplayName() {
		return 'Sales Tax';
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
		$rateLabel = sprintf('%s%%', $this->config('rate'));
		return StringUtilities::replaceTokens($this->config('label'), array( 'rate' => $rateLabel ));
	}

	/**
	 * @inheritdoc
	 *
	 * @param \Sitegear\Module\Customer\Model\TransactionItem[] $items
	 */
	public function getAdjustmentAmount(array $items, array $data) {
		$total = 0;
		foreach ($items as $item) {
			$total += $item->getUnitPrice() * $item->getQuantity();
		}
		$rate = $this->config('rate');
		return $this->isIncludedAmount() ?
				intval($total * $rate / (100 + $rate)) :
				intval($total * $rate / 100);
	}

	/**
	 * @inheritdoc
	 */
	public function isIncludedAmount() {
		return $this->config('included');
	}
}
