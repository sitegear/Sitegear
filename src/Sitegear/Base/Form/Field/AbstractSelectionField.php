<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Field;

abstract class AbstractSelectionField extends AbstractField implements SelectionFieldInterface {

	//-- SelectionFieldInterface Methods --------------------

	/**
	 * @return string[]
	 */
	public function getValues() {
		return $this->getSetting('values', array());
	}

	/**
	 * @param string $value
	 * @param string $label
	 *
	 * @return self
	 */
	public function addValue($value, $label) {
		$options = $this->getSetting('values', array());
		$options[] = array(
			'value' => $value,
			'label' => $label
		);
		$this->setSetting('values', $options);
		return $this;
	}

	/**
	 * @param string $value
	 *
	 * @return self
	 */
	public function removeValue($value) {
		$options = array_filter($this->getSetting('values', array()), function($item) use ($value) {
			return $item['value'] !== $value;
		});
		$this->setSetting('values', $options);
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isMultiple() {
		return $this->getSetting('multiple');
	}

}
