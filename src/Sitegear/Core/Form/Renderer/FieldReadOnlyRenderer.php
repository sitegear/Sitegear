<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Form\Renderer;

use Sitegear\Util\HtmlUtilities;

/**
 * Renders a field in read-only mode.
 */
class FieldReadOnlyRenderer extends AbstractFieldRenderer {

	//-- RendererInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function render(array & $output) {
		$value = $this->getField()->isArrayValue() ? implode(', ', $this->getField()->getValue()) : $this->getField()->getValue();
		return sprintf(
			'<%s%s>%s</%s>',
			$this->getRenderOption(self::RENDER_OPTION_KEY_ELEMENT_NAME),
			HtmlUtilities::attributes($this->getRenderOption(self::RENDER_OPTION_KEY_ATTRIBUTES)),
			strlen($value) > 0 ? $value : '&nbsp;',
			$this->getRenderOption(self::RENDER_OPTION_KEY_ELEMENT_NAME)
		);
	}

}
