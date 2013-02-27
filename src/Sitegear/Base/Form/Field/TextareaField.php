<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Field;

use Symfony\Component\Validator\Constraint;

/**
 * A field made up of a single `<textarea>` element.
 */
class TextareaField extends AbstractField {

	//-- FieldInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function isArrayValue() {
		return false;
	}

	//-- Public Methods --------------------

	/**
	 * @param int|null $cols
	 *
	 * @return self
	 */
	public function setCols($cols) {
		return $this->setSetting('cols', $cols);
	}

	/**
	 * @return int|null
	 */
	public function getCols() {
		return $this->getSetting('cols');
	}

	/**
	 * @param int|null $rows
	 *
	 * @return self
	 */
	public function setRows($rows) {
		return $this->setSetting('rows', $rows);
	}

	/**
	 * @return int|null
	 */
	public function getRows() {
		return $this->getSetting('rows');
	}

}
