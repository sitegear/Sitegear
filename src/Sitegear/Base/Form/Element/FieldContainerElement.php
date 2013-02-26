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
 * A container for a field element and its corresponding label element.
 */
class FieldContainerElement extends AbstractContainerElement {

	//-- Constructor --------------------

	public function __construct(StepInterface $step, $elementName=null, array $defaultAttributes=null, array $children=null) {
		$elementName = $elementName ?: 'div';
		$defaultAttributes = $defaultAttributes ?: array(
			'class' => 'field'
		);
		parent::__construct($step, $elementName, $defaultAttributes, $children);
	}

}
