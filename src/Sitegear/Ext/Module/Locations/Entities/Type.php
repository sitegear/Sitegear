<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Locations\Entities;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repositoryClass="Sitegear\Ext\Module\Locations\LocationsRepository")
 * @Table(name="locations_type")
 */
class Type {

	//-- Attributes --------------------

	/**
	 * @var int
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	private $id;

	/**
	 * @var integer
	 * @Column(type="integer")
	 */
	private $displaySequence;

	/**
	 * @var string
	 * @Column(type="string", nullable=false)
	 */
	private $name;

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
	 * @OneToMany(targetEntity="Item", mappedBy="type")
	 */
	private $items;

	//-- Constructor --------------------

	public function __construct() {
		$this->items = new ArrayCollection();
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
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getItems() {
		return $this->items;
	}

	/**
	 * @param int $displaySequence
	 */
	public function setDisplaySequence($displaySequence) {
		$this->displaySequence = $displaySequence;
	}

	/**
	 * @return int
	 */
	public function getDisplaySequence() {
		return $this->displaySequence;
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

}
