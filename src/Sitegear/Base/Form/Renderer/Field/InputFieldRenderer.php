<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer\Field;

use Sitegear\Base\Form\Renderer\Field\AbstractFieldRenderer;
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
			HtmlUtilities::attributes($this->getRenderOption('attributes'))
		);
	}

	//-- AbstractInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function normaliseRenderOptions(array $renderOptions=null) {
		$renderOptions = parent::normaliseRenderOptions($renderOptions);
		$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['type'] = !is_null($this->getField()->getType()) ? $this->getField()->getType() : 'text';
		$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['value'] = $renderOptions[self::RENDER_OPTION_KEY_VALUE];
		return $renderOptions;
	}

}
