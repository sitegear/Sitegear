<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer\Element;

/**
 * Defines the behaviour of an object responsible for rendering a HTML representation of a given type of element.
 *
 * Each implementation of ElementRendererInterface corresponds with an implementation of ElementInterface.  The
 * renderer implementation is named the same as the element implementation, with an additional child namespace
 * component of "Renderer" and a class name suffix of "Renderer".
 */
interface ElementRendererInterface {

	/**
	 * @return \Sitegear\Base\Form\Element\ElementInterface
	 */
	public function getElement();

	/**
	 * @return \Sitegear\Base\Form\Renderer\Factory\FormRendererFactoryInterface
	 */
	public function getFactory();

	/**
	 * @param array $options
	 *
	 * @return string[]
	 */
	public function render(array $options);

}
