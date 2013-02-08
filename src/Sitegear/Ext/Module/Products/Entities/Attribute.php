<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Products\Entities;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="products_attribute")
 */
class Attribute {

	//-- Attributes --------------------

	/**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	private $id;

	/**
	 * @var string
	 * @Column(type="string", nullable=false)
	 */
	private $name;

	/**
	 * @var boolean
	 * @Column(type="boolean")
	 */
	private $multiple;

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

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @OneToMany(targetEntity="AttributeAssignment", mappedBy="attribute")
	 */
	private $products;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @OneToMany(targetEntity="AttributeOption", mappedBy="attribute")
	 */
	private $options;

	//-- Constructor --------------------

	public function __construct() {
		$this->products = new ArrayCollection();
		$this->options = new ArrayCollection();
	}

	//-- Accessor Methods --------------------

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
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

	/**
	 * @param boolean $multiple
	 */
	public function setMultiple($multiple) {
		$this->multiple = $multiple;
	}

	/**
	 * @return boolean
	 */
	public function getMultiple() {
		return $this->multiple;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getProducts() {
		return $this->products;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getOptions() {
		return $this->options;
	}

}
