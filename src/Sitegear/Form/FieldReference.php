<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Form;

/**
 * Describes a reference made from a field group to a field, by name.  Additionally the reference may be a normal field
 * reference, or a read-only reference, which is used to display back previously entered values but not allow
 * modification of those values (e.g. final confirmation step).
 */
class FieldReference {

	//-- Attributes --------------------

	/**
	 * @var string
	 */
	private $fieldName;

	/**
	 * @var boolean
	 */
	private $readOnly;

	/**
	 * @var boolean
	 */
	private $wrapped;

	//-- Constructor --------------------

	/**
	 * @param string $fieldName
	 * @param boolean $readOnly
	 * @param boolean $wrapped
	 */
	public function __construct($fieldName, $readOnly, $wrapped) {
		$this->fieldName = $fieldName;
		$this->readOnly = $readOnly;
		$this->wrapped = $wrapped;
	}

	//-- FieldReferenceInterface Methods --------------------

	/**
	 * @return string
	 */
	public function getFieldName() {
		return $this->fieldName;
	}

	/**
	 * @return boolean
	 */
	public function isReadOnly() {
		return $this->readOnly;
	}

	/**
	 * @return boolean
	 */
	public function isWrapped() {
		return $this->wrapped;
	}

}
