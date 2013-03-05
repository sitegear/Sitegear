<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Form\Renderer;

use Sitegear\Base\Form\FormInterface;

/**
 * RendererInterface implementation for the top-level form element.
 */
class FormRenderer extends AbstractContainerRenderer {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Form\FormInterface
	 */
	private $form;

	/**
	 * @var integer
	 */
	private $step;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\Form\FormInterface $form
	 * @param integer $step
	 * @param array|null $renderOptions
	 */
	public function __construct(FormInterface $form, $step, array $renderOptions=null) {
		$this->form = $form;
		$this->step = intval($step);
		parent::__construct($renderOptions);
	}

	//-- Public Methods --------------------

	/**
	 * @return \Sitegear\Base\Form\FormInterface
	 */
	public function getForm() {
		return $this->form;
	}

	/**
	 * @return integer
	 */
	public function getStep() {
		return $this->step;
	}

	//-- AbstractRenderer Methods --------------------

	protected function renderChildren(array & $output) {
		foreach ($this->getForm()->getStep($this->getStep())->getFieldsets() as $fieldset) {
			// TODO Pass fieldset render options
			$fieldsetRenderer = new FieldsetRenderer($fieldset);
			$fieldsetRenderer->render($output);
		}
		$buttonsRenderer = new ButtonsRenderer($this->getForm());
		$buttonsRenderer->render($output);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function normaliseRenderOptions(array $renderOptions=null) {
		$renderOptions = parent::normaliseRenderOptions($renderOptions);
		// TODO Handle "class" attributes properly
		$renderOptions[self::RENDER_OPTION_KEY_ELEMENT_NAME] = 'form';
		$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES] = array_merge(
			$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES],
			array(
				'action' => $this->form->getSubmitUrl(),
				'method' => $this->form->getMethod()
			)
		);
		return $renderOptions;
	}

}
