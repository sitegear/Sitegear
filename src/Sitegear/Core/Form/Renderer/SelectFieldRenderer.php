<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Form\Renderer;

use Sitegear\Util\ArrayUtilities;
use Sitegear\Util\HtmlUtilities;

/**
 * Renderer for a `SelectField`.
 *
 * @method \Sitegear\Base\Form\Field\SelectField getField()
 */
class SelectFieldRenderer extends AbstractFieldRenderer {

	//-- RendererInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function render(array & $output) {
		$output[] = sprintf(
			'<select%s>',
			HtmlUtilities::attributes($this->getRenderOption(self::RENDER_OPTION_KEY_ATTRIBUTES))
		);
		$value = $this->getField()->getValue();
		foreach ($this->getField()->getValues() as $option) {
			$optionAttributes = array( 'value' => $option['value'] );
			if ($option['value'] === $value) {
				$optionAttributes['selected'] = 'selected';
			}
			$output[] = sprintf(
				'<option%s>%s</option>',
				HtmlUtilities::attributes($optionAttributes),
				$option['label']
			);
		}
		$output[] = '</select>';
	}

	//-- AbstractInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function normaliseRenderOptions() {
		$renderOptions = parent::normaliseRenderOptions();
		if ($this->getField()->isMultiple()) {
			$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['multiple'] = 'multiple';
		} elseif (isset($attributes['multiple'])) {
			unset($renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['multiple']);
		}
		return $renderOptions;
	}

}
