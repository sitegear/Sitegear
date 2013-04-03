<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Forms\Form\Renderer;

use Sitegear\Module\Forms\Form\Renderer\AbstractFieldRenderer;
use Sitegear\Util\ArrayUtilities;
use Sitegear\Util\HtmlUtilities;

/**
 * Renderer for an `InputField`.
 *
 * @method \Sitegear\Base\Form\Field\InputField getField()
 */
class InputFieldRenderer extends AbstractFieldRenderer {

	//-- RendererInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function render(array & $output) {
		$output[] = sprintf(
			'<input%s />',
			HtmlUtilities::attributes(ArrayUtilities::mergeHtmlAttributes(
				$this->getRenderOption('attributes'),
				array(
					'value' => $this->getFieldValue()
				)
			))
		);
	}

	//-- AbstractInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function normaliseRenderOptions() {
		return ArrayUtilities::combine(
			parent::normaliseRenderOptions(),
			array(
				self::RENDER_OPTION_KEY_ATTRIBUTES => array(
					'type' => $this->getField()->getType() ?: 'text'
				)
			)
		);
	}

}
