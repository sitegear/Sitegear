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
 * Renderer for a `ButtonsElement`.
 *
 * @method \Sitegear\Base\Form\Element\ButtonsElement getElement()
 */
class ButtonsElementRenderer extends AbstractContainerElementRenderer {

	//-- AbstractElementRenderer Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function renderChildren(array $options) {
		$result = array();
		$backButtonAttributes = $this->getElement()->getStep()->getForm()->getBackButtonAttributes();
		if (is_array($backButtonAttributes)) {
			$backButtonAttributes['type'] = 'submit';
			$backButtonAttributes['name'] = 'back';
			if (!isset($backButtonAttributes['value'])) {
				$backButtonAttributes['value'] = 'Back';
			}
			if ($this->getElement()->getStep()->getStepIndex() < 1) {
				$backButtonAttributes['disabled'] = 'disabled';
			}
			$result[] = sprintf('<input%s />', HtmlUtilities::attributes($backButtonAttributes));
		}
		$submitButtonAttributes = $this->getElement()->getStep()->getForm()->getSubmitButtonAttributes();
		if (is_array($submitButtonAttributes)) {
			$submitButtonAttributes['type'] = 'submit';
			$result[] = sprintf('<input%s />', HtmlUtilities::attributes($submitButtonAttributes));
		}
		$resetButtonAttributes = $this->getElement()->getStep()->getForm()->getResetButtonAttributes();
		if (is_array($resetButtonAttributes)) {
			$resetButtonAttributes['type'] = 'reset';
			$result[] = sprintf('<input%s />', HtmlUtilities::attributes($resetButtonAttributes));
		}
		return $result;
	}

}
