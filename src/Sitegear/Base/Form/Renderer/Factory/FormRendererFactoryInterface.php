<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer\Factory;

use Sitegear\Base\Form\Element\ElementInterface;
use Sitegear\Base\Form\Field\FieldInterface;

/**
 * Defines the behaviour of a factory for form renderers.
 */
interface FormRendererFactoryInterface {

	/**
	 * @param \Sitegear\Base\Form\Element\ElementInterface $element
	 *
	 * @return \Sitegear\Base\Form\Renderer\Element\ElementRendererInterface
	 */
	public function getElementRenderer(ElementInterface $element);

	/**
	 * @param \Sitegear\Base\Form\Field\FieldInterface $field
	 *
	 * @return \Sitegear\Base\Form\Renderer\Field\FieldRendererInterface
	 */
	public function getFieldRenderer(FieldInterface $field);

}
