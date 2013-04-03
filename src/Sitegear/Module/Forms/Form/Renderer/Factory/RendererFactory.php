<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Forms\Form\Renderer\Factory;

use Sitegear\Base\Form\FieldsetInterface;
use Sitegear\Base\Form\FormInterface;
use Sitegear\Base\Form\Field\FieldInterface;
use Sitegear\Base\Form\Renderer\Factory\RendererFactoryInterface;
use Sitegear\Module\Forms\Form\Renderer\ButtonsRenderer;
use Sitegear\Module\Forms\Form\Renderer\FieldErrorsRenderer;
use Sitegear\Module\Forms\Form\Renderer\FieldLabelRenderer;
use Sitegear\Module\Forms\Form\Renderer\FieldReadOnlyRenderer;
use Sitegear\Module\Forms\Form\Renderer\FieldWrapperReadOnlyRenderer;
use Sitegear\Module\Forms\Form\Renderer\FieldWrapperRenderer;
use Sitegear\Module\Forms\Form\Renderer\FieldsetRenderer;
use Sitegear\Module\Forms\Form\Renderer\FormRenderer;
use Sitegear\Module\Forms\Form\Renderer\InputFieldRenderer;
use Sitegear\Module\Forms\Form\Renderer\MultipleInputFieldRenderer;
use Sitegear\Module\Forms\Form\Renderer\SelectFieldRenderer;
use Sitegear\Module\Forms\Form\Renderer\TextareaFieldRenderer;
use Sitegear\Util\NameUtilities;

/**
 * Factory for generating a renderer based on a given field type.
 */
class RendererFactory implements RendererFactoryInterface {

	//-- Attributes --------------------

	private $renderOptionDefaults;

	//-- Constructor --------------------

	/**
	 * @param array|null $renderOptionDefaults
	 */
	public function __construct(array $renderOptionDefaults=null) {
		$this->renderOptionDefaults = $renderOptionDefaults ?: array();
	}

	//-- Accessor Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getRenderOptionDefaults($className) {
		return isset($this->renderOptionDefaults[$className]) ? $this->renderOptionDefaults[$className] : array();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setRenderOptionDefaults($className, array $renderOptionDefaults) {
		$this->renderOptionDefaults[$className] = $renderOptionDefaults;
		return $this;
	}

	//-- Factory Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function createFormRenderer(FormInterface $form, $step) {
		return new FormRenderer($this, $form, $step);
	}

	/**
	 * {@inheritDoc}
	 */
	public function createFieldsetRenderer(FieldsetInterface $fieldset) {
		return new FieldsetRenderer($this, $fieldset);
	}

	/**
	 * {@inheritDoc}
	 */
	public function createFieldWrapperRenderer(FieldInterface $field) {
		return new FieldWrapperRenderer($this, $field);
	}

	/**
	 * {@inheritDoc}
	 */
	public function createFieldWrapperReadOnlyRenderer(FieldInterface $field) {
		return new FieldWrapperReadOnlyRenderer($this, $field);
	}

	/**
	 * {@inheritDoc}
	 */
	public function createFieldLabelRenderer(FieldInterface $field) {
		return new FieldLabelRenderer($this, $field);
	}

	/**
	 * {@inheritDoc}
	 */
	public function createFieldErrorsRenderer(FieldInterface $field) {
		return new FieldErrorsRenderer($this, $field);
	}

	/**
	 * {@inheritDoc}
	 */
	public function createButtonsRenderer(FormInterface $form) {
		return new ButtonsRenderer($this, $form);
	}

	/**
	 * {@inheritDoc}
	 *
	 * This implementation directly maps InputField, TextareaField, SelectField and MultipleInputField to their known
	 * renderer implementations.  Otherwise, the field class name has 'Renderer' appended, which means the renderer
	 * implementation should be in the same namespace as the field implementation, to utilise custom field types.
	 */
	public function createFieldRenderer(FieldInterface $field) {
		$fieldClass = new \ReflectionClass($field);
		switch ($fieldClass->getShortName()) {
			case 'InputField':
				$renderer = new InputFieldRenderer($this, $field);
				break;
			case 'TextareaField':
				$renderer = new TextareaFieldRenderer($this, $field);
				break;
			case 'SelectField':
				$renderer = new SelectFieldRenderer($this, $field);
				break;
			case 'MultipleInputField':
				$renderer = new MultipleInputFieldRenderer($this, $field);
				break;
			default:
				$rendererClass = new \ReflectionClass($fieldClass->getName() . 'Renderer');
				$renderer = $rendererClass->newInstance($this, $field);
		}
		return $renderer;
	}

	/**
	 * {@inheritDoc}
	 */
	public function createFieldReadOnlyRenderer(FieldInterface $field) {
		return new FieldReadOnlyRenderer($this, $field);
	}

}
