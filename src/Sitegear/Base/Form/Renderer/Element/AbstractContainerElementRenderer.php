<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer\Element;

use Sitegear\Util\HtmlUtilities;

/**
 * @method \Sitegear\Base\Form\Element\AbstractContainerElement getElement()
 */
abstract class AbstractContainerElementRenderer extends AbstractElementRenderer {

	//-- AbstractElementRenderer Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function startRendering(array $options) {
		$attributes = array_merge(
			isset($options['attributes']) ? $options['attributes'] : array(),
			$this->getElement()->getDefaultAttributes()
		);
		return array(
			sprintf('<%s%s>', $this->getElement()->getElementName(), HtmlUtilities::attributes($attributes))
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function finishRendering(array $options) {
		return array(
			sprintf('</%s>', $this->getElement()->getElementName())
		);
	}

}
