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
 * @Table("customer_token")
 */
class Token {

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
	 * @ManyToOne(targetEntity="Account", inversedBy="tokens")
	 */
	private $account;

	/**
	 * @var string
	 * @Column(type="string", length=32),
	 */
	private $token;

	/**
	 * @var string
	 * @Column(type="string")
	 */
	private $purpose;

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
	 * @param string $purpose
	 */
	public function setPurpose($purpose) {
		$this->purpose = $purpose;
	}

	/**
	 * @return string
	 */
	public function getPurpose() {
		return $this->purpose;
	}

	/**
	 * @param string $token
	 */
	public function setToken($token) {
		$this->token = $token;
	}

	/**
	 * @return string
	 */
	public function getToken() {
		return $this->token;
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
