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
 * Renderer for a `TextareaField`.
 *
 * @method \Sitegear\Base\Form\Field\TextareaField getField()
 */
class TextareaFieldRenderer extends AbstractFieldRenderer {

	//-- RendererInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function render(array & $output) {
		$output[] = sprintf(
			'<textarea%s>%s</textarea>',
			HtmlUtilities::attributes($this->getRenderOption(self::RENDER_OPTION_KEY_ATTRIBUTES)),
			$this->getField()->getValue()
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
					'rows' => $this->getField()->getRows(),
					'cols' => $this->getField()->getCols()
				)
			)
		);
	}

}
