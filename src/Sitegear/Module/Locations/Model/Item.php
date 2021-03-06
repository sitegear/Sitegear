<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Locations\Model;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Sitegear\Module\Locations\Repository\ItemRepository")
 * @ORM\Table(name="locations_item")
 */
class Item {

	//-- Attributes --------------------

	/**
	 * @var int
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(type="string", unique=true, nullable=false)
	 */
	private $urlPath;

	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	private $displaySequence;

	/**
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	private $active;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=false)
	 */
	private $name;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $streetAddress;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $suburb;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $postcode;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $stateOrProvince;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $country;

	/**
	 * @var float
	 * @ORM\Column(type="decimal", precision=16, scale=10)
	 */
	private $latitude;

	/**
	 * @var float
	 * @ORM\Column(type="decimal", precision=16, scale=10)
	 */
	private $longitude;

	/**
	 * @var array
	 * @ORM\Column(type="json", nullable=false)
	 */
	private $mapOptions;

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

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @ORM\OneToMany(targetEntity="Detail", mappedBy="item")
	 */
	private $details;

	/**
	 * @var Region
	 * @ORM\ManyToOne(targetEntity="Region", inversedBy="items")
	 */
	private $region;

	/**
	 * @var Type
	 * @ORM\ManyToOne(targetEntity="Type", inversedBy="items")
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
	 * @param array $mapOptions
	 */
	public function setMapOptions($mapOptions) {
		$this->mapOptions = $mapOptions;
	}

	/**
	 * @return array
	 */
	public function getMapOptions() {
		return $this->mapOptions;
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
	 * @return \Sitegear\Module\Locations\Model\Region
	 */
	public function getRegion() {
		return $this->region;
	}

	/**
	 * @return \Sitegear\Module\Locations\Model\Type
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param float $latitude
	 */
	public function setLatitude($latitude) {
		$this->latitude = $latitude;
	}

	/**
	 * @return float
	 */
	public function getLatitude() {
		return $this->latitude;
	}

	/**
	 * @param float $longitude
	 */
	public function setLongitude($longitude) {
		$this->longitude = $longitude;
	}

	/**
	 * @return float
	 */
	public function getLongitude() {
		return $this->longitude;
	}

}
