<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\RealCaptcha\Form;

use Sitegear\Core\Module\Forms\Form\Renderer\AbstractFieldRenderer;
use Sitegear\Core\Module\Forms\Form\Renderer\InputFieldRenderer;
use Sitegear\Util\ArrayUtilities;
use Sitegear\Util\HtmlUtilities;

/**
 * Renderer for an `CaptchaField`.
 *
 * @method \Sitegear\Ext\Module\RealCaptcha\Form\RealCaptchaField getField()
 */
class RealCaptchaFieldRenderer extends InputFieldRenderer {

	//-- Constants --------------------

	/**
	 * Render option key used to specify the attributes for the `<img/>` element used for the captcha image.
	 */
	const RENDER_OPTION_KEY_CAPTCHA_IMAGE_ATTRIBUTES = 'captcha-image-attributes';

	//-- RendererInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function render(array & $output) {
		$output[] = sprintf('<%s%s />', 'img', HtmlUtilities::attributes(ArrayUtilities::combine(
			$this->getRenderOption(self::RENDER_OPTION_KEY_CAPTCHA_IMAGE_ATTRIBUTES),
			array(
				// TODO Pass this URL in from RealCaptchaModule
				'src' => 'real-captcha/image'
			)
		)));
		// TODO Optional reload button (basic javascript or jquery?)
		parent::render($output);
	}

	//-- AbstractInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function normaliseRenderOptions() {
		return ArrayUtilities::combine(
			array(
				self::RENDER_OPTION_KEY_CAPTCHA_IMAGE_ATTRIBUTES => array(
					'class' => 'sitegear-captcha-image'
				)
			),
			ArrayUtilities::combine(
				parent::normaliseRenderOptions(),
				array(
					self::RENDER_OPTION_KEY_ATTRIBUTES => array(
						'type' => $this->getField()->getType() ?: 'text'
					)
				)
			)
		);
	}

}
