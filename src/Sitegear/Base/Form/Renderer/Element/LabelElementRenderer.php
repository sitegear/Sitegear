<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer\Element;

use Sitegear\Base\Form\Element\ElementInterface;

/**
 * Renderer for a LabelElement.
 *
 * @method \Sitegear\Base\Form\Element\FieldElement getElement()
 */
class LabelElementRenderer extends AbstractElementRenderer {

	/**
	 * {@inheritDoc}
	 */
	protected function startRendering(array $options) {
		$field = $this->getElement()->getField();
		$glue = isset($options['glue']) ? $options['glue'] : null;
		return array(
			sprintf('<label for="%s">%s%s</label>', $field->getName(), $field->getLabelText(), $field->getLabelMarkers($glue))
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function finishRendering(array $options) {
		$result = array();
		$errors = $this->getElement()->getField()->getErrors();
		if (!empty($errors)) {
			// TODO Configurable elements and attributes
			$result[] = '<ul class="error-messages">';
			foreach ($errors as $errorMessage) {
				$result[] = sprintf('<li>%s</li>', $errorMessage);
			}
			$result[] = '</ul>';
		}
		return $result;
	}

}
