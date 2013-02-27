<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer\Element;

/**
 * Renderer for a `FieldElement`.
 *
 * @method \Sitegear\Base\Form\Element\FieldElement getElement()
 */
class FieldElementRenderer extends AbstractElementRenderer {

	/**
	 * {@inheritDoc}
	 */
	protected function startRendering(array $options) {
		$field = $this->getElement()->getField();
		$fieldRenderer = $this->getFactory()->getFieldRenderer($field);
		if ($this->getElement()->getFieldReference()->isReadOnly()) {
			// TODO Configurable string format
			$result = array();
			$result[] = sprintf('<span class="display">%s</span>', $field->isArrayValue() ? implode(', ', $field->getValue()) : $field->getValue());
			$value = $field->isArrayValue() ? $field->getValue() : array( $field->getValue() );
			foreach ($value as $valueElement) {
				$result[] = sprintf('<input type="hidden" name="%s" value="%s" />', $field->getName(), $valueElement);
			}
		} else {
			$result = $fieldRenderer->render($options, $this->getElement()->getField()->getValue());
		}
		return $result;
	}

}
