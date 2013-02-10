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
 * @Table(name="locations_item")
 */
class Item {

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
	 * @var string
	 * @Column(type="string", nullable=true)
	 */
	private $streetAddress;

	/**
	 * @var string
	 * @Column(type="string", nullable=true)
	 */
	private $suburb;

	/**
	 * @var string
	 * @Column(type="string", nullable=true)
	 */
	private $postcode;

	/**
	 * @var string
	 * @Column(type="string", nullable=true)
	 */
	private $stateOrProvince;

	/**
	 * @var string
	 * @Column(type="string", nullable=true)
	 */
	private $country;

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
	 * @var \Doctrine\Common\Collections\Collection
	 * @OneToMany(targetEntity="Detail", mappedBy="item")
	 */
	private $details;

	/**
	 * @var Region
	 * @ManyToOne(targetEntity="Region", inversedBy="items")
	 */
	private $region;

	/**
	 * @var Type
	 * @ManyToOne(targetEntity="Type", inversedBy="items")
	 */
	private $type;

	//-- Constructor --------------------

	public function __construct() {
		$this->details = new ArrayCollection();
	}

	//-- Accessor Methods --------------------

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
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
	 * @param string $country
	 */
	public function setCountry($country) {
		$this->country = $country;
	}

	/**
	 * @return string
	 */
	public function getCountry() {
		return $this->country;
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
	 * @param string $postcode
	 */
	public function setPostcode($postcode) {
		$this->postcode = $postcode;
	}

	/**
	 * @return string
	 */
	public function getPostcode() {
		return $this->postcode;
	}

	/**
	 * @param string $stateOrProvince
	 */
	public function setStateOrProvince($stateOrProvince) {
		$this->stateOrProvince = $stateOrProvince;
	}

	/**
	 * @return string
	 */
	public function getStateOrProvince() {
		return $this->stateOrProvince;
	}

	/**
	 * @param string $streetAddress
	 */
	public function setStreetAddress($streetAddress) {
		$this->streetAddress = $streetAddress;
	}

	/**
	 * @return string
	 */
	public function getStreetAddress() {
		return $this->streetAddress;
	}

	/**
	 * @param string $suburb
	 */
	public function setSuburb($suburb) {
		$this->suburb = $suburb;
	}

	/**
	 * @return string
	 */
	public function getSuburb() {
		return $this->suburb;
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
	public function getDetails() {
		return $this->details;
	}

	/**
	 * @return \Sitegear\Ext\Module\Locations\Entities\Region
	 */
	public function getRegion() {
		return $this->region;
	}

	/**
	 * @return \Sitegear\Ext\Module\Locations\Entities\Type
	 */
	public function getType() {
		return $this->type;
	}

}
