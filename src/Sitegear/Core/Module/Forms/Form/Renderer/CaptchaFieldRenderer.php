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
 * Renderer for an `CaptchaField`.
 *
 * @method \Sitegear\Base\Form\Field\CaptchaField getField()
 */
class CaptchaFieldRenderer extends InputFieldRenderer {

	//-- RendererInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function render(array & $output, array $values, array $errors) {
		// TODO Proper image HTML
		$output[] = '<img src="TODO" alt="" style="display:inline-block; width:200px; height:40px; background-color:#eee; border:1px solid #666;" />';
		parent::render($output, $values, $errors);
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
