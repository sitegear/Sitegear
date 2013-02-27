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
 * Renderer for an `InputField`.
 *
 * @method \Sitegear\Base\Form\Field\InputField getField()
 */
class InputFieldRenderer extends AbstractFieldRenderer {

	/**
	 * {@inheritDoc}
	 */
	public function render(array $options, $value) {
		$attributes = isset($options['attributes']) ? $options['attributes'] : array();
		$attributes['type'] = $this->getField()->getType();
		$attributes['id'] = isset($attributes['id']) ? $attributes['id'] : $this->getField()->getName();
		$attributes['name'] = $this->getField()->getName();
		$attributes['value'] = $value;
		return array(
			sprintf('<input%s />', HtmlUtilities::attributes($attributes) )
		);
	}

}
