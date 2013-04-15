<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Form\Renderer\Factory;

use Sitegear\Form\FieldsetInterface;
use Sitegear\Form\FormInterface;
use Sitegear\Form\Field\FieldInterface;
use Sitegear\Form\Renderer\RendererInterface;
use Sitegear\Form\Renderer\FieldRendererInterface;

/**
 * Describes the behaviour of a factory that generates RendererInterface implementations.
 */
interface RendererFactoryInterface {

	//-- Accessor Methods --------------------

	/**
	 * Retrieve the render option defaults for the given class name.
	 *
	 * @param string $className
	 *
	 * @return array
	 */
	public function getRenderOptionDefaults($className);

	/**
	 * Modify the render option defaults for the given class name.
	 *
	 * @param string $className
	 * @param array $renderOptionDefaults
	 *
	 * @return self
	 */
	public function setRenderOptionDefaults($className, array $renderOptionDefaults);

	//-- Factory Methods --------------------

	/**
	 * Create a top-level renderer for a form.
	 *
	 * @param FormInterface $form
	 * @param integer $step
	 *
	 * @return RendererInterface
	 */
	public function createFormRenderer(FormInterface $form, $step);

	/**
	 * Create a renderer for a fieldset.
	 *
	 * @param FieldsetInterface $fieldset
	 *
	 * @return RendererInterface
	 */
	public function createFieldsetRenderer(FieldsetInterface $fieldset);

	/**
	 * Create a renderer for a wrapper for a normal field.
	 *
	 * @param FieldInterface $field
	 *
	 * @return RendererInterface
	 */
	public function createFieldWrapperRenderer(FieldInterface $field);

	/**
	 * Create a renderer for a wrapper for a read-only field.
	 *
	 * @param FieldInterface $field
	 *
	 * @return RendererInterface
	 */
	public function createFieldWrapperReadOnlyRenderer(FieldInterface $field);

	/**
	 * Create a renderer for a field's label.
	 *
	 * @param FieldInterface $field
	 *
	 * @return RendererInterface
	 */
	public function createFieldLabelRenderer(FieldInterface $field);

	/**
	 * Create a renderer for a field's error messages.
	 *
	 * @param FieldInterface $field
	 *
	 * @return RendererInterface
	 */
	public function createFieldErrorsRenderer(FieldInterface $field);

	/**
	 * Create a renderer for the buttons panel.
	 *
	 * @param FormInterface $form
	 *
	 * @return RendererInterface
	 */
	public function createButtonsRenderer(FormInterface $form);

	/**
	 * Create a field renderer for this field wrapper's field type.
	 *
	 * @param \Sitegear\Form\Field\FieldInterface $field
	 *
	 * @return \Sitegear\Form\Renderer\FieldRendererInterface
	 */
	public function createFieldRenderer(FieldInterface $field);

	/**
	 * Create a renderer for a read-only field.
	 *
	 * @param \Sitegear\Form\Field\FieldInterface $field
	 *
	 * @return \Sitegear\Form\Renderer\FieldRendererInterface
	 */
	public function createFieldReadOnlyRenderer(FieldInterface $field);

}
