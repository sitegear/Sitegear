<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Customer\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table("customer_token")
 */
class Token {

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
	 * @ORM\ManyToOne(targetEntity="Account", inversedBy="tokens")
	 */
	private $account;

	/**
	 * @var string
	 * @ORM\Column(type="string", length=32),
	 */
	private $token;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	private $purpose;

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
	 * @param \Sitegear\Module\Customer\Model\Account $account
	 */
	public function setAccount($account) {
		$this->account = $account;
	}

	/**
	 * @return \Sitegear\Module\Customer\Model\Account
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
