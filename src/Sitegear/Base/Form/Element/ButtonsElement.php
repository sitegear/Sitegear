<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Element;

use Sitegear\Base\Form\StepInterface;
use Sitegear\Base\Form\Field\InputField;

class ButtonsElement extends AbstractContainerElement {

	//-- Attributes --------------------

	private $submitButton;
	private $cancelButton;

	//-- Constructor --------------------

	public function __construct(StepInterface $step, $elementName=null, array $defaultAttributes=null) {
		$elementName = $elementName ?: 'div';
		$defaultAttributes = $defaultAttributes ?: array(
			'class' => 'buttons'
		);
		parent::__construct($step, $elementName, $defaultAttributes, null);
		$submitButtonAttributes = $step->getForm()->getSubmitButtonAttributes();
		if (!is_array($submitButtonAttributes)) {
			$submitButtonAttributes = array( 'value' => $submitButtonAttributes );
		}
		$submitButtonValue = isset($submitButtonAttributes['value']) ? $submitButtonAttributes['value'] : null;
		$this->submitButton = new InputField(null, $submitButtonValue);
		$resetButtonAttributes = $step->getForm()->getResetButtonAttributes();
		if ($resetButtonAttributes) {
			if (!is_array($resetButtonAttributes)) {
				$resetButtonAttributes = array( 'value' => $resetButtonAttributes );
			}
			$resetButtonValue = isset($resetButtonAttributes['value']) ? $resetButtonAttributes['value'] : null;
			$this->resetButton = new InputField(null, $resetButtonValue);
		} else {
			$this->resetButton = null;
		}
	}

	//-- ElementInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getChildren() {
		return array();
	}

	/**
	 * {@inheritDoc}
	 */
	public function addChild(ElementInterface $child, $index = null) {
		throw new \LogicException(sprintf('%s does not support dynamic children', get_class($this)));
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeChild($child) {
		throw new \LogicException(sprintf('%s does not support dynamic children', get_class($this)));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAncestorFields() {
		return array();
	}

}
