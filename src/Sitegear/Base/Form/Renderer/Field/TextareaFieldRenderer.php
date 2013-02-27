<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer\Field;

use Sitegear\Util\HtmlUtilities;

/**
 * Renderer for a `TextareaField`.
 *
 * @method \Sitegear\Base\Form\Field\TextareaField getField()
 */
class TextareaFieldRenderer extends AbstractFieldRenderer {

	/**
	 * {@inheritDoc}
	 */
	public function render(array $options, $value) {
		$attributes = isset($options['attributes']) ? $options['attributes'] : array();
		$attributes['id'] = isset($attributes['id']) ? $attributes['id'] : $this->getField()->getName();
		$attributes['name'] = $this->getField()->getName();
		if (!is_null($this->getField()->getRows())) {
			$attributes['rows'] = $this->getField()->getRows();
		}
		if (!is_null($this->getField()->getCols())) {
			$attributes['cols'] = $this->getField()->getCols();
		}
		return array(
			sprintf('<textarea%s>%s</textarea>', HtmlUtilities::attributes($attributes), $value)
		);
	}

}
