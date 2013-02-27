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
 * Renderer for a `SelectField`.
 *
 * @method \Sitegear\Base\Form\Field\SelectField getField()
 */
class SelectFieldRenderer extends AbstractFieldRenderer {

	/**
	 * {@inheritDoc}
	 */
	public function render(array $options, $value) {
		$attributes = isset($options['attributes']) ? $options['attributes'] : array();
		if ($this->getField()->isMultiple()) {
			$attributes['multiple'] = 'multiple';
		} elseif (isset($attributes['multiple'])) {
			unset($attributes['multiple']);
		}
		$result = array();
		$result[] = sprintf(
			'<select id="%s" name="%s"%s>',
			isset($attributes['id']) ? $attributes['id'] : $this->getField()->getName(),
			$this->getField()->getName(),
			HtmlUtilities::attributes($attributes, array( 'name', 'id' ))
		);
		foreach ($this->getField()->getValues() as $option) {
			$result[] = sprintf(
				'<option value="%s"%s>%s</option>',
				$option['value'],
				($option['value'] === $value ? ' selected="selected"' : ''),
				$option['label']
			);
		}
		$result[] = '</select>';
		return $result;
	}

}
