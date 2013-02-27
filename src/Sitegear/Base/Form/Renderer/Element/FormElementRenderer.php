<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer\Element;

use Sitegear\Base\Form\Field\FieldInterface;

/**
 * Renderer for a `FormElement`.
 *
 * @method \Sitegear\Base\Form\Element\FormElement getElement()
 */
class FormElementRenderer extends AbstractContainerElementRenderer {

	protected function startRendering(array $options) {
		$result = parent::startRendering($options);
		// Add hidden field for step counter
		$result[] = sprintf('<input type="hidden" name="step" value="%d" />', $this->getElement()->getStep()->getStepIndex());
		// Add error heading if necessary
		$errorFields = array_filter($this->getElement()->getStep()->getRootElement()->getAncestorFields(), function(FieldInterface $field) {
			return sizeof($field->getErrors()) > 0;
		});
		if (sizeof($errorFields) > 0) {
			$result[] = $this->getElement()->getStep()->getErrorHeading();
		}
		return $result;
	}

}
