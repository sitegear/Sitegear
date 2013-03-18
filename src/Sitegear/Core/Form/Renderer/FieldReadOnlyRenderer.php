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
		if ($this->getField()->isArrayValue()) {
			$output[] = $this->renderValue(implode(', ', $this->getField()->getValue()));
		} else {
			$output[] = $this->renderValue($this->getField()->getValue());
		}
	}

	//-- AbstractRenderer Methods --------------------

	public function normaliseRenderOptions() {
		$renderOptions = parent::normaliseRenderOptions();
		$renderOptions[self::RENDER_OPTION_KEY_ELEMENT_NAME] = 'span';
		if (!isset($renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['class'])) {
			$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['class'] = 'display';
		}
		return $renderOptions;
	}

	//-- Internal Methods --------------------

	/**
	 * @param $value
	 *
	 * @return string
	 */
	protected function renderValue($value) {
		return sprintf(
			'<%s%s>%s</%s>',
			$this->getRenderOption(self::RENDER_OPTION_KEY_ELEMENT_NAME),
			HtmlUtilities::attributes($this->getRenderOption(self::RENDER_OPTION_KEY_ATTRIBUTES)),
			strlen($value) > 0 ? $value : '&nbsp;',
			$this->getRenderOption(self::RENDER_OPTION_KEY_ELEMENT_NAME)
		);
	}

}
