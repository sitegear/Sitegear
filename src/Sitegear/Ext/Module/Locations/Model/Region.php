<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Locations\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="locations_region")
 */
class Region {

	//-- Attributes --------------------

	/**
	 * @var int
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	private $id;

	/**
	 * @var string
	 * @Column(type="string", unique=true, nullable=false)
	 */
	private $urlPath;

	/**
	 * @var integer
	 * @Column(type="integer")
	 */
	private $displaySequence;

	/**
	 * @var boolean
	 * @Column(type="boolean")
	 */
	private $active;

	/**
	 * @var string
	 * @Column(type="string", nullable=false)
	 */
	private $name;

	/**
	 * @var array
	 * @Column(type="array", nullable=false)
	 */
	private $mapdata;

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
	 * @var Region
	 * @ManyToOne(targetEntity="Region", inversedBy="children")
	 */
	private $parent;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @OneToMany(targetEntity="Region", mappedBy="parent")
	 */
	private $children;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @OneToMany(targetEntity="Item", mappedBy="region")
	 */
	private $items;

	//-- Constructor --------------------

	public function __construct() {
		$this->items = new ArrayCollection();
		$this->children = new ArrayCollection();
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
	 * @param boolean $active
	 */
	public function setActive($active) {
		$this->active = $active;
	}

	/**
	 * @return boolean
	 */
	public function getActive() {
		return $this->active;
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
	 * @param array $mapdata
	 */
	public function setMapdata($mapdata) {
		$this->mapdata = $mapdata;
	}

	/**
	 * @return array
	 */
	public function getMapdata() {
		return $this->mapdata;
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
	 * @param string $urlPath
	 */
	public function setUrlPath($urlPath) {
		$this->urlPath = $urlPath;
	}

	/**
	 * @return string
	 */
	public function getUrlPath() {
		return $this->urlPath;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getItems() {
		return $this->items;
	}

	/**
	 * @return \Sitegear\Ext\Module\Locations\Model\Region
	 */
	public function getParent() {
		return $this->parent;
	}

}
