<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Form\Field;

/**
 * A single-element `<input>` type field.
 */
class InputField extends AbstractField {

	//-- FieldInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function isArrayValue() {
		return false;
	}

	//-- Public Methods --------------------

	/**
	 * @return string
	 */
	public function getType() {
		return $this->getSetting('type');
	}

	/**
	 * @param string $type
	 */
	public function setType($type) {
		$this->setSetting('type', $type);
	}

}
