<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Element;

use Sitegear\Base\Form\StepInterface;

/**
 * Element that represents the top-level `<form>` element.
 */
class FormElement extends AbstractContainerElement {

	//-- Constructor --------------------

	public function __construct(StepInterface $step, array $defaultAttributes=null, array $children=null) {
		$defaultAttributes = $defaultAttributes ?: array(
			'method' => 'post',
			'action' => $step->getForm()->getSubmitUrl()
		);
		parent::__construct($step, 'form', $defaultAttributes, $children);
	}

}
