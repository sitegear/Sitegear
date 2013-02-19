<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Customer\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table("customer_account")
 */
class Account {

	//-- Attributes --------------------

	/**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	private $id;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @OneToMany(targetEntity="AccountFieldValue", mappedBy="account")
	 */
	private $fieldValues;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @OneToMany(targetEntity="Transaction", mappedBy="account")
	 */
	private $transactions;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @OneToMany(targetEntity="Token", mappedBy="account")
	 */
	private $tokens;

	/**
	 * Matches the `User` package email address.
	 *
	 * @var string
	 * @Column(type="string", unique=true)
	 */
	private $email;

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

	//-- Constructor --------------------

	public function __construct() {
		$this->fieldValues = new ArrayCollection();
		$this->transactions = new ArrayCollection();
		$this->tokens = new ArrayCollection();
	}

	//-- Accessor Methods --------------------

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getFieldValues() {
		return $this->fieldValues;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getTokens() {
		return $this->tokens;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getTransactions() {
		return $this->transactions;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateModified() {
		return $this->dateModified;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateCreated() {
		return $this->dateCreated;
	}

}