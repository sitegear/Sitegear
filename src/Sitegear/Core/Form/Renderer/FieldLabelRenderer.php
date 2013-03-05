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
 * Renders the label for a given field.
 */
class FieldLabelRenderer extends AbstractFieldRenderer {

	//-- Constants --------------------

	const RENDER_OPTION_KEY_MARKER_SEPARATOR = 'marker-separator';

	//-- RendererInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function render(array & $output) {
		$output[] = sprintf(
			'<label%s>%s%s</label>',
			HtmlUtilities::attributes($this->getRenderOption(self::RENDER_OPTION_KEY_ATTRIBUTES)),
			$this->getField()->getLabelText(),
			$this->getField()->getLabelMarkers($this->getRenderOption(self::RENDER_OPTION_KEY_MARKER_SEPARATOR))
		);
	}

	//-- AbstractRenderer Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function normaliseRenderOptions(array $renderOptions=null) {
		$renderOptions = parent::normaliseRenderOptions($renderOptions);
		if (!isset($renderOptions[self::RENDER_OPTION_KEY_MARKER_SEPARATOR])) {
			$renderOptions[self::RENDER_OPTION_KEY_MARKER_SEPARATOR] = '';
		}
		return $renderOptions;
	}

}
