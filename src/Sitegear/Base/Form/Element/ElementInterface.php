<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Element;

/**
 * Defines the behaviour of elements within a form structure.  HTML elements such as <form>, <fieldset>, and the actual
 * field container itself, are represented by an implementation of this interface.
 *
 * Each implementation of ElementInterface corresponds with an implementation of ElementRendererInterface.  The
 * renderer implementation is named the same as the element implementation, with an additional child namespace
 * component of "Renderer" and a class name suffix of "Renderer".
 */
interface ElementInterface {

	/**
	 * @return \Sitegear\Base\Form\StepInterface Reference back to the step that contains this element.
	 */
	public function getStep();

	/**
	 * @return ElementInterface[]
	 */
	public function getChildren();

	/**
	 * @param ElementInterface $child
	 * @param integer|null $index
	 *
	 * @return self
	 */
	public function addChild(ElementInterface $child, $index=null);

	/**
	 * @param ElementInterface|integer $child
	 *
	 * @return self
	 */
	public function removeChild($child);

	/**
	 * Get all the fields from this element and all children.
	 *
	 * @return \Sitegear\Base\Form\Field\FieldInterface[]
	 */
	public function getAncestorFields();

}
