<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\Forms\Form\Renderer;

use Sitegear\Core\Module\Forms\Form\Renderer\AbstractRenderer;
use Sitegear\Util\HtmlUtilities;
use string;

/**
 * Base class for renderers of container elements.  This splits the rendering into three sections -- start tag,
 * children and end tag.
 */
abstract class AbstractContainerRenderer extends AbstractRenderer {

	//-- RendererInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function render(array & $output, array $values, array $errors) {
		$this->renderStartTag($output, $values, $errors);
		$this->renderChildren($output, $values, $errors);
		$this->renderEndTag($output, $values, $errors);
	}

	//-- Internal Methods --------------------

	/**
	 * Render the start tag.
	 *
	 * @param string[] $output
	 * @param array $values
	 * @param array[] $errors
	 */
	protected function renderStartTag(array & $output, array $values, array $errors) {
		$output[] = sprintf(
			'<%s%s>',
			$this->getRenderOption(self::RENDER_OPTION_KEY_ELEMENT_NAME),
			HtmlUtilities::attributes($this->getRenderOption(self::RENDER_OPTION_KEY_ATTRIBUTES))
		);
	}

	/**
	 * Render the children of this element.  This will vary according to the form hierarchy, for example the children
	 * of a `<form>` element are `<fieldset>` elements.
	 *
	 * @param string[] $output
	 * @param array $values
	 * @param array[] $errors
	 */
	protected function renderChildren(array & $output, array $values, array $errors) {
		// Default implementation does nothing
	}

	/**
	 * Render the end tag.
	 *
	 * @param string[] $output
	 * @param array $values
	 * @param array[] $errors
	 */
	protected function renderEndTag(array & $output, array $values, array $errors) {
		$output[] = sprintf(
			'</%s>',
			$this->getRenderOption(self::RENDER_OPTION_KEY_ELEMENT_NAME)
		);
	}

}
