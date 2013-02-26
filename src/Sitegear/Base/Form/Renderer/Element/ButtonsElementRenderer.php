<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer\Element;

use Sitegear\Util\HtmlUtilities;

class ButtonsElementRenderer extends AbstractContainerElementRenderer {

	//-- AbstractElementRenderer Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function renderChildren(array $options) {
		$result = array();
		$submitButtonAttributes = $this->getElement()->getStep()->getForm()->getSubmitButtonAttributes();
		if (is_array($submitButtonAttributes)) {
			$result[] = sprintf('<input type="submit"%s />', HtmlUtilities::attributes($submitButtonAttributes, array( 'type' )));
		}
		$resetButtonAttributes = $this->getElement()->getStep()->getForm()->getResetButtonAttributes();
		if (is_array($resetButtonAttributes)) {
			$result[] = sprintf('<input type="reset"%s />', HtmlUtilities::attributes($resetButtonAttributes, array( 'type' )));
		}
		return $result;
	}

}
