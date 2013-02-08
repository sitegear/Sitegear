<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Products\Entities;

/**
 * @Entity
 * @Table(name="products_category_assignment")
 */
class CategoryAssignment {

	//-- Attributes --------------------

	/**
	 * @var integer
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
	 * @var Item
	 * @ManyToOne(targetEntity="Item", inversedBy="categories")
	 */
	private $item;

	/**
	 * @var Category
	 * @ManyToOne(targetEntity="Category", inversedBy="items")
	 */
	private $category;

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
	 * @return Category
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 * @return int
	 */
	public function getItem() {
		return $this->item;
	}

}
