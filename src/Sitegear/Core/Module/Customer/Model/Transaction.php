<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\Customer\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table("customer_transaction")
 */
class Transaction {

	//-- Attributes --------------------

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	private $id;

	/**
	 * @var Account
	 * @ORM\ManyToOne(targetEntity="Account", inversedBy="transactions")
	 */
	private $account;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @ORM\OneToMany(targetEntity="TransactionItem", mappedBy="transaction")
	 */
	private $items;

	/**
	 * @var string
	 * @ORM\Column(type="string", length=48, nullable=false)
	 */
	private $clientIp;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	private $datePurchased;

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
	 * @param \Sitegear\Core\Module\Customer\Model\Account $account
	 */
	public function setAccount($account) {
		$this->account = $account;
	}

	/**
	 * @return \Sitegear\Core\Module\Customer\Model\Account
	 */
	public function getAccount() {
		return $this->account;
	}

	/**
	 * @param string $clientIp
	 */
	public function setClientIp($clientIp) {
		$this->clientIp = $clientIp;
	}

	/**
	 * @return string
	 */
	public function getClientIp() {
		return $this->clientIp;
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $items
	 */
	public function setItems($items) {
		$this->items = $items;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getItems() {
		return $this->items;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatePurchased() {
		return $this->datePurchased;
	}

	/**
	 * @param \DateTime $datePurchased
	 */
	public function setDatePurchased($datePurchased) {
		$this->datePurchased = $datePurchased;
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
