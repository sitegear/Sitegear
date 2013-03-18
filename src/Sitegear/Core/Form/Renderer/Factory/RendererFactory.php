<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Form\Renderer\Factory;

use Sitegear\Base\Form\FieldsetInterface;
use Sitegear\Base\Form\FormInterface;
use Sitegear\Base\Form\Field\FieldInterface;
use Sitegear\Base\Form\Renderer\Factory\RendererFactoryInterface;
use Sitegear\Core\Form\Renderer\ButtonsRenderer;
use Sitegear\Core\Form\Renderer\FieldErrorsRenderer;
use Sitegear\Core\Form\Renderer\FieldLabelRenderer;
use Sitegear\Core\Form\Renderer\FieldReadOnlyRenderer;
use Sitegear\Core\Form\Renderer\FieldWrapperReadOnlyRenderer;
use Sitegear\Core\Form\Renderer\FieldWrapperRenderer;
use Sitegear\Core\Form\Renderer\FieldsetRenderer;
use Sitegear\Core\Form\Renderer\FormRenderer;
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
	 */
	public function createFieldRenderer(FieldInterface $field) {
		$fieldClass = new \ReflectionClass($field);
		$fieldRendererClass = new \ReflectionClass(sprintf('\\Sitegear\\Core\\Form\\Renderer\\%sRenderer', $fieldClass->getShortName()));
		return $fieldRendererClass->newInstance($this, $field);
	}

	/**
	 * {@inheritDoc}
	 */
	public function createFieldReadOnlyRenderer(FieldInterface $field) {
		return new FieldReadOnlyRenderer($this, $field);
	}

}
