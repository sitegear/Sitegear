<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Element;

use Sitegear\Base\Form\StepInterface;
use Sitegear\Base\Form\FieldReference;
use Sitegear\Base\Form\Field\FieldInterface;

/**
 * Abstract implementation of an element that represents a field, or part of a field.
 */
abstract class AbstractFieldElement extends AbstractElement {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Form\FieldReference
	 */
	private $fieldReference;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\Form\StepInterface $step
	 * @param \Sitegear\Base\Form\FieldReference $fieldReference
	 */
	public function __construct(StepInterface $step, FieldReference $fieldReference) {
		parent::__construct($step);
		$this->fieldReference = $fieldReference;
	}

	//-- ElementInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getChildren() {
		return array();
	}

	/**
	 * {@inheritDoc}
	 */
	public function addChild(ElementInterface $child, $index = null) {
		throw new \LogicException(sprintf('%s does not support children', get_class($this)));
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeChild($child) {
		throw new \LogicException(sprintf('%s does not support children', get_class($this)));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAncestorFields() {
		return array(
			$this->getField()
		);
	}

	//-- Public Methods --------------------

	/**
	 * @return \Sitegear\Base\Form\FieldReference
	 */
	public function getFieldReference() {
		return $this->fieldReference;
	}

	/**
	 * Shortcut method to `FormInterface::getField()`.
	 *
	 * @return \Sitegear\Base\Form\Field\FieldInterface
	 */
	public function getField() {
		return $this->getStep()->getForm()->getField($this->getFieldReference()->getFieldName());
	}

}
