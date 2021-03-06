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
 * Renderer for a `TextareaField`.
 *
 * @method \Sitegear\Form\Field\TextareaField getField()
 */
class TextareaFieldRenderer extends AbstractFieldRenderer {

	//-- RendererInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function render(array & $output) {
		$output[] = sprintf(
			'<textarea%s>%s</textarea>',
			HtmlUtilities::attributes($this->getRenderOption(self::RENDER_OPTION_KEY_ATTRIBUTES)),
			$this->getFieldValue()
		);
	}

	//-- AbstractInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	protected function normaliseRenderOptions() {
		return ArrayUtilities::combine(
			parent::normaliseRenderOptions(),
			array(
				self::RENDER_OPTION_KEY_ATTRIBUTES => array(
					'rows' => $this->getField()->getRows(),
					'cols' => $this->getField()->getCols()
				)
			)
		);
	}

}
