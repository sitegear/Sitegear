<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\Forms\Form\Renderer;

use Sitegear\Base\Form\FormInterface;
use Sitegear\Base\Form\Renderer\Factory\RendererFactoryInterface;
use Sitegear\Core\Module\Forms\Form\Renderer\AbstractContainerRenderer;
use Sitegear\Util\ArrayUtilities;

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
	 * @param RendererFactoryInterface $factory
	 * @param FormInterface $form
	 * @param integer $step
	 */
	public function __construct(RendererFactoryInterface $factory, FormInterface $form, $step) {
		$this->form = $form;
		$this->step = intval($step);
		parent::__construct($factory);
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

	/**
	 * {@inheritDoc}
	 */
	protected function renderChildren(array & $output) {
		foreach ($this->getForm()->getStep($this->getStep())->getFieldsets() as $fieldset) {
			$fieldsetRenderer = $this->getFactory()->createFieldsetRenderer($fieldset);
			$fieldsetRenderer->render($output);
		}
		$buttonsRenderer = $this->getFactory()->createButtonsRenderer($this->getForm());
		$buttonsRenderer->render($output);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function normaliseRenderOptions() {
		return ArrayUtilities::combine(
			parent::normaliseRenderOptions(),
			array(
				self::RENDER_OPTION_KEY_ELEMENT_NAME => 'form',
				self::RENDER_OPTION_KEY_ATTRIBUTES => array(
					'action' => $this->form->getSubmitUrl(),
					'method' => $this->form->getMethod()
				)
			)
		);
	}

}
