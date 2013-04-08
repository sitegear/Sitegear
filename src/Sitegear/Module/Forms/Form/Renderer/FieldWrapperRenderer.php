<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Forms\Form\Renderer;

use Sitegear\Base\Form\Field\FieldInterface;
use Sitegear\Base\Form\Renderer\Factory\RendererFactoryInterface;
use Sitegear\Module\Forms\Form\Renderer\AbstractContainerRenderer;
use Sitegear\Util\ArrayUtilities;
use Sitegear\Util\TypeUtilities;

/**
 * RendererInterface implementation for a wrapper around a single field.  The children rendered by this renderer are
 * the field's label, error messages, and the field itself.  The field renderer is determined dynamically based on the
 * class name of the field implementation.
 */
class FieldWrapperRenderer extends AbstractContainerRenderer {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Form\Field\FieldInterface
	 */
	private $field;

	//-- Constructor --------------------

	/**
	 * @param RendererFactoryInterface $factory
	 * @param FieldInterface $field
	 */
	public function __construct(RendererFactoryInterface $factory, FieldInterface $field) {
		$this->field = $field;
		parent::__construct($factory);
	}

	//-- Public Methods --------------------

	/**
	 * @return \Sitegear\Base\Form\Field\FieldInterface
	 */
	public function getField() {
		return $this->field;
	}

	//-- AbstractRenderer Methods --------------------

	/**
	 * @inheritdoc
	 */
	protected function renderChildren(array & $output) {
		$fieldLabelRenderer = $this->getFieldLabelRenderer();
		$fieldLabelRenderer->render($output);
		$fieldErrorsRenderer = $this->getFieldErrorsRenderer();
		$fieldErrorsRenderer->render($output);
		$fieldRenderer = $this->getFieldRenderer();
		$fieldRenderer->render($output);
	}

	/**
	 * @inheritdoc
	 */
	protected function normaliseRenderOptions() {
		$options = parent::normaliseRenderOptions();
		$options['attributes'] = ArrayUtilities::mergeHtmlAttributes(
			array( 'id' => $this->getField()->getName() . '-field' ),
			$options['attributes']
		);
		return $options;
	}

	//-- Internal Methods --------------------

	protected function getFieldLabelRenderer() {
		return $this->getFactory()->createFieldLabelRenderer($this->getField());
	}

	protected function getFieldErrorsRenderer() {
		return $this->getFactory()->createFieldErrorsRenderer($this->getField());
	}

	protected function getFieldRenderer() {
		return $this->getFactory()->createFieldRenderer($this->getField());
	}

}
