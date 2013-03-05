<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer;

use Sitegear\Base\Form\Renderer\AbstractContainerRenderer;
use Sitegear\Base\Form\Renderer\Field\Factory\FieldRendererFactory;
use Sitegear\Base\Form\Renderer\Field\FieldErrorsRenderer;
use Sitegear\Base\Form\Renderer\Field\FieldLabelRenderer;
use Sitegear\Base\Form\Field\FieldInterface;
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

	public function __construct(FieldInterface $field, array $renderOptions=null) {
		$this->field = $field;
		parent::__construct($renderOptions);
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
	 * {@inheritDoc}
	 *
	 * TODO Pass through render options
	 */
	protected function renderChildren(array & $output) {
		$fieldLabelRenderer = new FieldLabelRenderer($this->getField());
		$fieldLabelRenderer->render($output);
		$fieldErrorsRenderer = new FieldErrorsRenderer($this->getField());
		$fieldErrorsRenderer->render($output);
		$fieldRenderer = $this->getFieldRenderer();
		$fieldRenderer->render($output);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function normaliseRenderOptions(array $renderOptions=null) {
		$renderOptions = parent::normaliseRenderOptions($renderOptions);
		if (!isset($renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES])) {
			$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES] = array();
		}
		if (!isset($renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['class'])) {
			$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['class'] = 'field';
		}
		return $renderOptions;
	}

	//-- Internal Methods --------------------

	/**
	 * @return \Sitegear\Base\Form\Renderer\Field\FieldRendererInterface
	 *
	 * TODO Pass through render options
	 */
	protected function getFieldRenderer() {
		$fieldRendererFactory = new FieldRendererFactory();
		return $fieldRendererFactory->getFieldRenderer($this->getField(), array());
	}

}
