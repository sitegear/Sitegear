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
 * @Table("customer_master")
 */
class Customer {

	//-- Attributes --------------------

	/**
	 * @var int
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	private $id;

	/**
	 * Coupled to the `User` package email address.
	 *
	 * @var string
	 * @Column(type="string", unique=true)
	 */
	private $email;

	/**
	 * @var array
	 * @Column(type="json")
	 */
	private $fields;

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
		$this->fields = array();
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
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getField($key) {
		return array_key_exists($key, $this->fields) ? $this->fields[$key] : null;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function setField($key, $value) {
		$this->fields[$key] = $value;
	}

	/**
	 * @param string $key
	 */
	public function removeField($key) {
		if (array_key_exists($key, $this->fields)) {
			unset($this->fields[$key]);
		}
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
