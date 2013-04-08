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
 * Renders the error messages for a given field.
 */
class FieldErrorsRenderer extends AbstractFieldRenderer {

	//-- Constants --------------------

	/**
	 * Render option key used to specify the element name for the wrapper around each error message.
	 */
	const RENDER_OPTION_KEY_ERROR_WRAPPER_ELEMENT_NAME = 'error-wrapper-element-name';

	/**
	 * Render option key used to specify the attributes for the wrapper around each error message..
	 */
	const RENDER_OPTION_KEY_ERROR_WRAPPER_ATTRIBUTES = 'error-wrapper-attributes';

	//-- RendererInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function render(array & $output) {
		$name = $this->getField()->getName();
		$errors = $this->getField()->getForm()->getFieldErrors($name);
		if (!empty($errors)) {
			$output[] = sprintf('<%s%s>', $this->getRenderOption(self::RENDER_OPTION_KEY_ELEMENT_NAME), HtmlUtilities::attributes($this->getRenderOption(self::RENDER_OPTION_KEY_ATTRIBUTES)));
			$errorWrapperElementName = $this->getRenderOption(self::RENDER_OPTION_KEY_ERROR_WRAPPER_ELEMENT_NAME);
			$errorWrapperAttributes = HtmlUtilities::attributes($this->getRenderOption(self::RENDER_OPTION_KEY_ERROR_WRAPPER_ATTRIBUTES));
			foreach ($errors as $error) {
				$output[] = sprintf('<%s%s>%s</%s>', $errorWrapperElementName, $errorWrapperAttributes, $error, $errorWrapperElementName);
			}
			$output[] = sprintf('</%s>', $this->getRenderOption(self::RENDER_OPTION_KEY_ELEMENT_NAME));
		}
	}

	//-- AbstractRenderer Methods --------------------

	protected function normaliseRenderOptions() {
		return ArrayUtilities::combine(
			array(
				self::RENDER_OPTION_KEY_ERROR_WRAPPER_ELEMENT_NAME => 'div',
				self::RENDER_OPTION_KEY_ERROR_WRAPPER_ATTRIBUTES => array()
			),
			parent::normaliseRenderOptions()
		);
	}

}
