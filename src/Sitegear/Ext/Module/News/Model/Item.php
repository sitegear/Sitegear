<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\News\Model;

/**
 * @Entity(repositoryClass="Sitegear\Ext\Module\News\Repository\ItemRepository")
 * @Table("news_item")
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
	 * @var string
	 * @Column(type="string", nullable=false)
	 */
	private $headline;

	/**
	 * @var \DateTime
	 * @Column(type="datetime", nullable=true)
	 */
	private $datePublished;

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

	//-- Accessor Methods --------------------

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param \DateTime $datePublished
	 */
	public function setDatePublished($datePublished) {
		$this->datePublished = $datePublished;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatePublished() {
		return $this->datePublished;
	}

	/**
	 * @param string $headline
	 */
	public function setHeadline($headline) {
		$this->headline = $headline;
	}

	/**
	 * @return string
	 */
	public function getHeadline() {
		return $this->headline;
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
