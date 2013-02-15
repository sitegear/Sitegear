<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Module;

/**
 * Describes the behaviour of a module that provides items that can be purchased.
 */
interface PurchaseItemProviderModuleInterface extends ModuleInterface {

	/**
	 * Get the label to use when displaying the specified item.
	 *
	 * @param string $type
	 * @param int $id
	 *
	 * @return string
	 */
	public function getPurchaseItemLabel($type, $id);

	/**
	 * Get the available attributes and the allowed values in a nested array structure.  The top-level array is an
	 * indexed array, each element is a key-value array representing a single attribute.  The key-value arrays have
	 * keys "label" and "values" where the "values" key is a key-value array of id (value) to label.
	 *
	 * @param string $type
	 * @param int $id
	 *
	 * @return array[]
	 */
	public function getPurchaseItemAttributeDefinitions($type, $id);

	/**
	 * Get the unit price for the specified item, in whole cents.
	 *
	 * @param string $type
	 * @param int $id
	 * @param array $attributeValues
	 *
	 * @return int
	 */
	public function getPurchaseItemUnitPrice($type, $id, array $attributeValues);

}
