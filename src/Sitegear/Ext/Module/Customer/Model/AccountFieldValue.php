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
 * @Table("customer_account_field_value")
 */
class AccountFieldValue {

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
	 * @ManyToOne(targetEntity="Account", inversedBy="values")
	 */
	private $account;

	/**
	 * @var Field
	 * @ManyToOne(targetEntity="Field")
	 */
	private $field;

	/**
	 * @var string
	 * @Column(type="string", nullable=false)
	 */
	private $value;

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
	 * @param \Sitegear\Ext\Module\Customer\Model\Field $field
	 */
	public function setField($field) {
		$this->field = $field;
	}

	/**
	 * @return \Sitegear\Ext\Module\Customer\Model\Field
	 */
	public function getField() {
		return $this->field;
	}

	/**
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
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
