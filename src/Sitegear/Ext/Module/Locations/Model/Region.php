<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Locations\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="locations_region")
 */
class Region {

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
	 * @var Region
	 * @ORM\ManyToOne(targetEntity="Region", inversedBy="children")
	 */
	private $parent;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @ORM\OneToMany(targetEntity="Region", mappedBy="parent")
	 */
	private $children;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @ORM\OneToMany(targetEntity="Item", mappedBy="region")
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
