<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Customer;

use Sitegear\Base\Module\PurchaseItemProviderModuleInterface;
use Sitegear\Module\Customer\Model\TransactionItem;
use Sitegear\Util\LoggerRegistry;
use Sitegear\Util\TokenUtilities;

/**
 * Represents the customer's selected items.
 */
class Trolley {

	//-- Constants  --------------------

	/**
	 * Session key to use for the trolley contents.
	 */
	const SESSION_KEY_TROLLEY = 'customer.trolley';

	//-- Attributes --------------------

	/**
	 * @var CustomerModule
	 */
	private $customerModule;

	/**
	 * @var string[]
	 */
	private $messages;

	//-- Constructor --------------------

	/**
	 * @param CustomerModule $customerModule
	 * @param string[] $messages
	 */
	public function __construct(CustomerModule $customerModule, array $messages) {
		$this->customerModule = $customerModule;
		$this->messages = $messages;
	}

	//-- Public Methods --------------------

	/**
	 * Add a single item (of any quantity) to the trolley.
	 *
	 * @param string $moduleName Name of the module that provides the item being added.
	 * @param string $type Name of the item type being added.
	 * @param int $itemId Unique identifier of the item being added.
	 * @param array $attributeValues Attribute selections, a key-value array where the keys are attribute identifiers and
	 *   the values are value identifiers.
	 * @param int $quantity Quantity being added, 1 by default.
	 *
	 * @throws \InvalidArgumentException
	 * @throws \DomainException
	 */
	public function addItem($moduleName, $type, $itemId, array $attributeValues=null, $quantity=null) {
		LoggerRegistry::debug('Trolley::addItem');
		if ($quantity < 1) {
			throw new \DomainException('Trolley cannot modify trolley item to a zero or negative quantity; use removeTrolleyItem instead.');
		}
		$module = $this->getPurchaseItemProviderModule($moduleName);
		$attributeDefinitions = $module->getPurchaseItemAttributeDefinitions($type, $itemId);
		$attributes = array();

		// Get an array of attributes, which each have a value and a label.
		foreach ($attributeValues as $attributeValue) {
			$attributeValue = intval($attributeValue);
			foreach ($attributeDefinitions as $attributeDefinition) {
				foreach ($attributeDefinition['values'] as $value) {
					if ($value['id'] === $attributeValue) {
						$attributes[] = array(
							'value' => $attributeValue,
							'label' => sprintf('%s: %s', $attributeDefinition['label'], $value['label'])
						);
					}
				}
			}
		}

		// Add the item data to the trolley, or merge it in to an existing matching item.
		$data = $this->getData();
		$matched = false;
		foreach ($data as $index => $item) {
			/** @var TransactionItem $item */
			if (($item->getModule() === $moduleName) && ($item->getType() === $type) && ($item->getAttributes() === $attributes)) {
				$matched = $index;
			}
		}
		if ($matched !== false) {
			$item = $data[$matched];
			$item->setQuantity($item->getQuantity() + $quantity);
			$data[$matched] = $item;
		} else {
			$item = new TransactionItem();
			$item->setModule($moduleName);
			$item->setType($type);
			$item->setItemId($itemId);
			$item->setLabel($module->getPurchaseItemLabel($type, $itemId));
			$item->setDetailsUrl($module->getPurchaseItemDetailsUrl($type, $itemId, $attributeValues));
			$item->setAttributes($attributes);
			$item->setUnitPrice($module->getPurchaseItemUnitPrice($type, $itemId, $attributeValues));
			$item->setQuantity($quantity);
			$data[] = $item;
		}
		$this->setData($data);

		// Notify on next page load
		$this->customerModule->getEngine()->pageMessages()->add(TokenUtilities::replaceTokens($this->messages['item-added'], array( 'label' => $item->getLabel(), 'quantity' => $quantity )), 'success');
		if ($item->getQuantity() > $quantity) {
			$this->customerModule->getEngine()->pageMessages()->add(TokenUtilities::replaceTokens($this->messages['item-total'], array( 'label' => $item->getLabel(), 'quantity' => $item->getQuantity() )), 'success');
		}
	}

	/**
	 * Remove the trolley item at the given index.
	 *
	 * @param $index
	 *
	 * @throws \OutOfBoundsException
	 */
	public function removeItem($index) {
		LoggerRegistry::debug('Trolley::removeItem');
		$data = $this->getData();
		if ($index < 0 || $index >= sizeof($data)) {
			throw new \OutOfBoundsException(sprintf('Trolley cannot remove trolley item with index (%d) out-of-bounds', $index));
		}

		// Modify the session data.
		/** @var TransactionItem $item */
		$item = array_splice($data, $index, 1)[0];
		$this->setData($data);

		// Notify on next page load.
		$this->customerModule->getEngine()->pageMessages()->add(TokenUtilities::replaceTokens($this->messages['item-removed'], array( 'label' => $item->getLabel() )), 'success');
	}

	/**
	 * Set the quantity of the trolley item at the given index.  The quantity must be greater than zero.
	 *
	 * @param $index
	 * @param $quantity
	 *
	 * @throws \DomainException
	 * @throws \OutOfBoundsException
	 */
	public function modifyItem($index, $quantity) {
		LoggerRegistry::debug('Trolley::modifyItem');
		if ($quantity < 1) {
			throw new \DomainException('Trolley cannot modify trolley item to a zero or negative quantity; use removeTrolleyItem instead.');
		}
		$data = $this->getData();
		if ($index < 0 || $index >= sizeof($data)) {
			throw new \OutOfBoundsException(sprintf('Trolley cannot modify trolley item with index (%d) out-of-bounds', $index));
		}

		// Modify the session data.
		$item = $data[$index]; /** @var TransactionItem $item */
		$item->setQuantity($quantity);
		$data[$index] = $item;
		$this->setData($data);

		// Notify on next page load.
		$this->customerModule->getEngine()->pageMessages()->add(TokenUtilities::replaceTokens($this->messages['item-modified'], array( 'label' => $item->getLabel(), 'quantity' => $quantity )), 'success');
	}

	/**
	 * Get the current contents of the trolley.
	 *
	 * @return TransactionItem[]
	 */
	public function getData() {
		return $this->customerModule->getEngine()->getSession()->get(self::SESSION_KEY_TROLLEY, array());
	}

	/**
	 * Set the contents of the trolley.
	 *
	 * @param TransactionItem[] $data
	 */
	public function setData(array $data) {
		$this->customerModule->getEngine()->getSession()->set(self::SESSION_KEY_TROLLEY, $data);
	}

	//-- Internal Methods --------------------

	/**
	 * Retrieve a named module from the engine and check that it is an instance of PurchaseItemProviderModuleInterface.
	 * Essentially this is a shortcut to getEngine()->getModule() with an additional type check.
	 *
	 * @param $name
	 *
	 * @return \Sitegear\Base\Module\PurchaseItemProviderModuleInterface
	 * @throws \InvalidArgumentException
	 */
	protected function getPurchaseItemProviderModule($name) {
		$module = $this->customerModule->getEngine()->getModule($name);
		if (!$module instanceof PurchaseItemProviderModuleInterface) {
			throw new \InvalidArgumentException(sprintf('The specified module "%s" is not a valid purchase item provider.', $name));
		}
		return $module;
	}

}
