<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer\Field;

/**
 * Defines the behaviour of a renderer for a field.
 */
interface FieldRendererInterface {

	/**
	 * @return \Sitegear\Base\Form\Field\FieldInterface
	 */
	public function getField();

	/**
	 * @return \Sitegear\Base\Form\Renderer\Factory\FormRendererFactoryInterface
	 */
	public function getFactory();

	/**
	 * @param array $options
	 * @param mixed $value
	 *
	 * @return string[]
	 */
	public function render(array $options, $value);

}
