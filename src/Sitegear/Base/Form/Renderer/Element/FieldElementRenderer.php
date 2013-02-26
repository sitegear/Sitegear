<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer\Element;

/**
 * Renderer for a FieldElement.
 *
 * @method \Sitegear\Base\Form\Element\FieldElement getElement()
 */
class FieldElementRenderer extends AbstractElementRenderer {

	/**
	 * {@inheritDoc}
	 */
	protected function startRendering(array $options) {
		$fieldRenderer = $this->getFactory()->getFieldRenderer($this->getElement()->getField());
		return $fieldRenderer->render($options, $this->getElement()->getField()->getValue());
	}

}
