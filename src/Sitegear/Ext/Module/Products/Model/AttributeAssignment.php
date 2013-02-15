<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Products\Model;

/**
 * @Entity
 * @Table(name="products_attribute_assignment")
 */
class AttributeAssignment {

	//-- Attributes --------------------

	/**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	private $id;

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
	 * @ManyToOne(targetEntity="Item", inversedBy="attributes")
	 */
	private $item;

	/**
	 * @var Attribute
	 * @ManyToOne(targetEntity="Attribute", inversedBy="products")
	 */
	private $attribute;

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
	 * @return Attribute
	 */
	public function getAttribute() {
		return $this->attribute;
	}

	/**
	 * @return Item
	 */
	public function getItem() {
		return $this->item;
	}

}
