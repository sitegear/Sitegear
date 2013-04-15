<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Form\Field;

/**
 * A field made up of zero or more `<input>` elements as an implementation of `SelectionFieldInterface`.  Each value is
 * represented by either a checkbox or a radio button, depending on whether the field allows multiple values.
 */
class MultipleInputField extends AbstractSelectionField {

	//-- FieldInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function isArrayValue() {
		return true;
	}

	//-- Public Methods --------------------

	/**
	 * @return string
	 */
	public function getType() {
		return $this->isMultiple() ? 'checkbox' : 'radio';
	}

}
