<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Locations\Entities;

/**
 * @Entity(repositoryClass="Sitegear\Ext\Module\Locations\LocationsRepository")
 * @Table(name="locations_detail")
 */
class Detail {

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
	private $label;

	/**
	 * @var string
	 * @Column(type="string", nullable=false)
	 */
	private $detail;

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
	 * @ManyToOne(targetEntity="Item", inversedBy="details")
	 */
	private $item;

	//-- Accessor Methods --------------------

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return \Sitegear\Ext\Module\Locations\Entities\Item
	 */
	public function getItem() {
		return $this->item;
	}

	/**
	 * @param string $detail
	 */
	public function setDetail($detail) {
		$this->detail = $detail;
	}

	/**
	 * @return string
	 */
	public function getDetail() {
		return $this->detail;
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
	 * @param string $label
	 */
	public function setLabel($label) {
		$this->label = $label;
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
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

}
