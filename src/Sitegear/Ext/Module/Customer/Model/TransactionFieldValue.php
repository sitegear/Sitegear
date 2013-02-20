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
 * @ORM\Table("customer_transaction_field_value")
 */
class TransactionFieldValue {

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
	 * @ORM\ManyToOne(targetEntity="Transaction", inversedBy="values")
	 */
	private $transaction;

	/**
	 * @var Field
	 * @ORM\ManyToOne(targetEntity="Field")
	 */
	private $field;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=false)
	 */
	private $value;

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
