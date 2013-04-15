<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Customer\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table("customer_account")
 */
class Account {

	//-- Attributes --------------------

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	private $id;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @ORM\OneToMany(targetEntity="AccountFieldValue", mappedBy="account")
	 */
	private $fieldValues;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @ORM\OneToMany(targetEntity="Transaction", mappedBy="account")
	 */
	private $transactions;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @ORM\OneToMany(targetEntity="Token", mappedBy="account")
	 */
	private $tokens;

	/**
	 * Matches the `User` package email address.
	 *
	 * @var string
	 * @ORM\Column(type="string", unique=true)
	 */
	private $email;

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
	 * @param string $name
	 *
	 * @return null|AccountFieldValue
	 */
	public function getNamedFieldValue($name) {
		$result = null;
		foreach ($this->fieldValues as $fieldValue) { /** @var AccountFieldValue $fieldValue */
			if ($fieldValue->getField()->getName() === $name) {
				$result = $fieldValue;
			}
		}
		return $result;
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
