<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Customer\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table("customer_transaction_item")
 */
class TransactionItem {

	//-- Attributes --------------------

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	private $id;

	/**
	 * @var Transaction
	 * @ORM\ManyToOne(targetEntity="Transaction", inversedBy="items")
	 */
	private $transaction;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=false)
	 */
	private $module;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=false)
	 */
	private $type;

	/**
	 * @var integer
	 * @ORM\Column(type="integer", nullable=false)
	 */
	private $itemId;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=false)
	 */
	private $label;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	private $detailsUrl;

	/**
	 * @var array
	 * @ORM\Column(type="json")
	 */
	private $attributes;

	/**
	 * @var integer
	 * @ORM\Column(type="integer", nullable=false)
	 */
	private $unitPrice;

	/**
	 * @var integer
	 * @ORM\Column(type="integer", nullable=false)
	 */
	private $quantity;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime", nullable=false)
	 * @Gedmo\Timestampable(on="create")
	 */
	private $dateCreated;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime", nullable=true)
	 * @Gedmo\Timestampable(on="update")
	 */
	private $dateModified;

	//-- Accessor Methods --------------------

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param array $attributes
	 */
	public function setAttributes($attributes) {
		$this->attributes = $attributes;
	}

	/**
	 * @return array
	 */
	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * @param string $detailsUrl
	 */
	public function setDetailsUrl($detailsUrl) {
		$this->detailsUrl = $detailsUrl;
	}

	/**
	 * @return string
	 */
	public function getDetailsUrl() {
		return $this->detailsUrl;
	}

	/**
	 * @param int $itemId
	 */
	public function setItemId($itemId) {
		$this->itemId = $itemId;
	}

	/**
	 * @return int
	 */
	public function getItemId() {
		return $this->itemId;
	}

	/**
	 * @param string $label
	 */
	public function setLabel($label) {
		$this->label = $label;
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @param string $module
	 */
	public function setModule($module) {
		$this->module = $module;
	}

	/**
	 * @return string
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * @param int $quantity
	 */
	public function setQuantity($quantity) {
		$this->quantity = $quantity;
	}

	/**
	 * @return int
	 */
	public function getQuantity() {
		return $this->quantity;
	}

	/**
	 * @param \Sitegear\Ext\Module\Customer\Model\Transaction $transaction
	 */
	public function setTransaction($transaction) {
		$this->transaction = $transaction;
	}

	/**
	 * @return \Sitegear\Ext\Module\Customer\Model\Transaction
	 */
	public function getTransaction() {
		return $this->transaction;
	}

	/**
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param int $unitPrice
	 */
	public function setUnitPrice($unitPrice) {
		$this->unitPrice = $unitPrice;
	}

	/**
	 * @return int
	 */
	public function getUnitPrice() {
		return $this->unitPrice;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateCreated() {
		return $this->dateCreated;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateModified() {
		return $this->dateModified;
	}

}
