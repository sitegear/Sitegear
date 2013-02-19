<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Customer\Model;

/**
 * @Entity
 * @Table("customer_transaction")
 */
class Transaction {

	//-- Attributes --------------------

	/**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	private $id;

	/**
	 * @var Account
	 * @ManyToOne(targetEntity="Account", inversedBy="transactions")
	 */
	private $account;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @OneToMany(targetEntity="TransactionItem", mappedBy="transaction")
	 */
	private $items;

	/**
	 * @var string
	 * @Column(type="string", length=48, nullable=false)
	 */
	private $clientIp;

	/**
	 * @var \DateTime
	 * @Column(type="datetime", nullable=false)
	 * @Timestampable(on="create")
	 */
	private $dateCreated;

	/**
	 * @var \DateTime
	 * @Column(type="datetime", nullable=true)
	 * @Timestampable(on="update")
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
	 * @param \Sitegear\Ext\Module\Customer\Model\Account $account
	 */
	public function setAccount($account) {
		$this->account = $account;
	}

	/**
	 * @return \Sitegear\Ext\Module\Customer\Model\Account
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
