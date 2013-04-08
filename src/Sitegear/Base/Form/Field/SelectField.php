<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Field;

/**
 * A field that consists of a `<select>` element and its parent `<option>` elements.
 */
class SelectField extends AbstractSelectionField {

	//-- FieldInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function isArrayValue() {
		return $this->isMultiple();
	}

}
