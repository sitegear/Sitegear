<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Module;

/**
 * Defines the behaviour of a module that provides a price adjustment, which is applied to the trolley contents as a
 * whole and may be dependent on other information entered by the user.  This is intended to support adjustments such
 * as tax, shipping, and discount codes.
 */
interface PurchaseAdjustmentProviderModuleInterface extends ModuleInterface {

	/**
	 * Get the label to use on the trolley table and during the checkout process to represent this adjustment.
	 *
	 * @return string
	 */
	public function getAdjustmentLabel();

	/**
	 * Get the adjustment amount based on the given items and data which is taken from the checkout form.
	 *
	 * @param \Sitegear\Ext\Module\Customer\Model\TransactionItem[] $items
	 * @param array $data
	 *
	 * @return mixed
	 */
	public function getAdjustmentAmount(array $items, array $data);

}
