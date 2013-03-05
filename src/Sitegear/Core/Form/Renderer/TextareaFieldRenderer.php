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
			$this->getRenderOption(self::RENDER_OPTION_KEY_VALUE)
		);
	}

	//-- AbstractInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function normaliseRenderOptions(array $renderOptions=null) {
		$renderOptions = parent::normaliseRenderOptions($renderOptions);
		if (!is_null($this->getField()->getRows())) {
			$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['rows'] = $this->getField()->getRows();
		}
		if (!is_null($this->getField()->getCols())) {
			$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['cols'] = $this->getField()->getCols();
		}
		return $renderOptions;
	}

}
