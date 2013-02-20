<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Products\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="products_attribute")
 */
class Attribute {

	//-- Constants --------------------

	const TYPE_BASE = 'base';
	const TYPE_SINGLE = 'single';
	const TYPE_MULTIPLE = 'multiple';

	private static $validTypes = array( self::TYPE_BASE, self::TYPE_SINGLE, self::TYPE_MULTIPLE );

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
	 * @ORM\Column(type="string", nullable=false)
	 */
	private $label;

	/**
	 * Must be one of the TYPE_* constants defined in this class.
	 *
	 * @var string
	 * @ORM\Column(type="string", nullable=false)
	 */
	private $type;

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
	 * @ORM\OneToMany(targetEntity="AttributeAssignment", mappedBy="attribute")
	 */
	private $products;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @ORM\OneToMany(targetEntity="AttributeOption", mappedBy="attribute")
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
	 * @param string $type Must be one of the TYPE_* constants defined in this class.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setType($type) {
		if (!in_array($type, self::$validTypes)) {
			$validTypes = implode('", "', self::$validTypes);
			throw new \InvalidArgumentException(sprintf('Attribute cannot handle invalid type "%s", expecting one of: "%s"', $type, $validTypes));
		}
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param string $name
	 */
	public function setLabel($name) {
		$this->label = $name;
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
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
