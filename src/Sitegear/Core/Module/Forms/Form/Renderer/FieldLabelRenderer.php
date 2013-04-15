<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\Forms\Form\Renderer;

use Sitegear\Core\Module\Forms\Form\Renderer\AbstractFieldRenderer;
use Sitegear\Util\ArrayUtilities;
use Sitegear\Util\HtmlUtilities;

/**
 * Renders the label for a given field.
 */
class FieldLabelRenderer extends AbstractFieldRenderer {

	//-- Constants --------------------

	/**
	 * Render option key used to specify the separator between label markers, and between the label and the first
	 * label marker.
	 */
	const RENDER_OPTION_KEY_MARKER_SEPARATOR = 'marker-separator';

	//-- RendererInterface Methods --------------------

	/**
	 * @inheritdoc
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
	 * @inheritdoc
	 */
	protected function normaliseRenderOptions() {
		return ArrayUtilities::combine(
			array(
				self::RENDER_OPTION_KEY_MARKER_SEPARATOR => ''
			),
			parent::normaliseRenderOptions()
		);
	}

}
