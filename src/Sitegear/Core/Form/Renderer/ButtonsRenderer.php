<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Form\Renderer;

use Sitegear\Base\Form\FormInterface;
use Sitegear\Base\Form\Renderer\Factory\RendererFactoryInterface;
use Sitegear\Util\HtmlUtilities;

/**
 * Renders the buttons panel for a form.  This may include one or more of: submit button (`type="submit"` and no
 * `value` attribute), back button (`type="submit"` and a `value` attribute of "back"), reset button (`type="reset"`).
 */
class ButtonsRenderer extends AbstractContainerRenderer {

	//-- Constructor --------------------

	/**
	 * @param RendererFactoryInterface $factory
	 * @param FormInterface $form
	 */
	public function __construct(RendererFactoryInterface $factory, FormInterface $form) {
		$this->form = $form;
		parent::__construct($factory);
	}

	//-- Public Methods --------------------

	/**
	 * @return \Sitegear\Base\Form\FormInterface
	 */
	public function getForm() {
		return $this->form;
	}

	//-- AbstractContainerRenderer Methods --------------------

	protected function renderChildren(array & $output) {
		// Back button.
		$backButtonAttributes = $this->getForm()->getBackButtonAttributes();
		if (is_array($backButtonAttributes)) {
			$backButtonAttributes['type'] = 'submit';
			$backButtonAttributes['name'] = 'back';
			if (!isset($backButtonAttributes['value'])) {
				$backButtonAttributes['value'] = 'Back';
			}
			$output[] = sprintf('<input%s />', HtmlUtilities::attributes($backButtonAttributes));
		}
		// Submit button.
		$submitButtonAttributes = $this->getForm()->getSubmitButtonAttributes();
		$submitButtonAttributes['type'] = 'submit';
		$output[] = sprintf('<input%s />', HtmlUtilities::attributes($submitButtonAttributes));
		// Reset button.
		$resetButtonAttributes = $this->getForm()->getResetButtonAttributes();
		if (is_array($resetButtonAttributes)) {
			$resetButtonAttributes['type'] = 'reset';
			$output[] = sprintf('<input%s />', HtmlUtilities::attributes($resetButtonAttributes));
		}
	}

}
