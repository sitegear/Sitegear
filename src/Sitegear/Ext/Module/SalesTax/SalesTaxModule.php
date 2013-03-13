<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\SalesTax;

use Sitegear\Base\Module\AbstractConfigurableModule;
use Sitegear\Base\Module\PurchaseAdjustmentProviderModuleInterface;
use Sitegear\Util\TokenUtilities;

/**
 * Provides sales tax calculation and management tools for sales tax.
 */
class SalesTaxModule extends AbstractConfigurableModule implements PurchaseAdjustmentProviderModuleInterface {

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Sales Tax';
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
		$rateLabel = sprintf('%s%%', $this->config('rate'));
		return TokenUtilities::replaceTokens($this->config('label'), array( 'rate' => $rateLabel ));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param \Sitegear\Ext\Module\Customer\Model\TransactionItem[] $items
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
	 * {@inheritDoc}
	 */
	public function isIncludedAmount() {
		return $this->config('included');
	}
}
