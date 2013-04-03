<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\Products\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="products_category")
 */
class Category {

	//-- Attributes --------------------

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(type="string", unique=true)
	 */
	private $urlPath;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=false)
	 */
	private $name;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	private $displaySequence;

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
	 * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
	 */
	private $children;

	/**
	 * @var Category
	 * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
	 */
	private $parent;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @ORM\OneToMany(targetEntity="CategoryAssignment", mappedBy="category")
	 * @ORM\OrderBy({"displaySequence"="ASC"})
	 */
	private $itemAssignments;

	//-- Constructor --------------------

	public function __construct() {
		$this->itemAssignments = new ArrayCollection();
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
	 * @param string $displaySequence
	 */
	public function setDisplaySequence($displaySequence) {
		$this->displaySequence = $displaySequence;
	}

	/**
	 * @return string
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

	/**
	 * @param Category $parent
	 */
	public function setParent($parent) {
		$this->parent = $parent;
	}

	/**
	 * @return Category
	 */
	public function getParent() {
		return $this->parent;
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
	public function getItemAssignments() {
		return $this->itemAssignments;
	}

}