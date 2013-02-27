<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Element;

use Sitegear\Base\Form\StepInterface;

/**
 * Abstract base class representing an element which contains other elements as children.  These elements do not
 * directly contain fields; instead they contain ancestor elements which themselves may contain fields.
 */
abstract class AbstractContainerElement extends AbstractElement {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Form\Element\ElementInterface[]
	 */
	private $children;

	/**
	 * @var string
	 */
	private $elementName;

	/**
	 * @var string[]
	 */
	private $defaultAttributes;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\Form\StepInterface $step
	 * @param string $elementName
	 * @param string[]|null $defaultAttributes
	 * @param \Sitegear\Base\Form\Element\ElementInterface[]|null $children
	 */
	public function __construct(StepInterface $step, $elementName, array $defaultAttributes=null, array $children=null) {
		parent::__construct($step);
		$this->elementName = $elementName;
		$this->defaultAttributes = $defaultAttributes ?: array();
		$this->children = $children ?: array();
	}

	//-- ElementInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addChild(ElementInterface $child, $index=null) {
		if (is_null($index)) {
			$this->children[] = $child;
		} else {
			$this->children = array_splice($this->children, intval($index), 0, $child);
		}
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeChild($child) {
		$index = is_integer($child) ? $child : array_search($child, $this->children);
		$this->children = array_splice($this->children, $index, 1);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAncestorFields() {
		$result = array();
		foreach ($this->getChildren() as $child) {
			$result = array_merge($result, $child->getAncestorFields());
		}
		return $result;
	}

	//-- Public Methods --------------------

	/**
	 * @return string HTML element name.
	 */
	public function getElementName() {
		return $this->elementName;
	}

	/**
	 * @return string[] Key-value array of strings representing HTML attributes.
	 */
	public function getDefaultAttributes() {
		return $this->defaultAttributes;
	}

}
