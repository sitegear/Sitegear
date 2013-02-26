<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Field;

class MultipleInputField extends AbstractSelectionField {

	//-- FieldInterface Methods --------------------

	/**
	 * {@inheritDoc}
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
