<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\Forms\Form\Renderer;

use Sitegear\Base\Form\FieldsetInterface;
use Sitegear\Base\Form\Renderer\Factory\RendererFactoryInterface;
use Sitegear\Core\Module\Forms\Form\Renderer\AbstractContainerRenderer;
use Sitegear\Core\Module\Forms\Form\Renderer\Factory\RendererFactory;
use Sitegear\Util\ArrayUtilities;

/**
 * RendererInterface implementation for a fieldset.
 */
class FieldsetRenderer extends AbstractContainerRenderer {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Form\FieldsetInterface
	 */
	private $fieldset;

	//-- Constructor --------------------

	/**
	 * @param RendererFactoryInterface $factory
	 * @param FieldsetInterface $fieldset
	 */
	public function __construct(RendererFactoryInterface $factory, FieldsetInterface $fieldset) {
		$this->fieldset = $fieldset;
		parent::__construct($factory);
	}

	//-- Public Methods --------------------

	/**
	 * @return \Sitegear\Base\Form\FieldsetInterface
	 */
	public function getFieldset() {
		return $this->fieldset;
	}

	//-- AbstractRenderer Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function renderChildren(array & $output) {
		$form = $this->getFieldset()->getStep()->getForm();
		if (!is_null($this->getFieldset()->getHeading())) {
			$output[] = sprintf('<legend>%s</legend>', $this->getFieldset()->getHeading());
		}
		foreach ($this->getFieldset()->getFieldReferences() as $fieldReference) {
			$field = $form->getField($fieldReference->getFieldName());
			if ($fieldReference->isWrapped()) {
				// isWrapped() is true, so render a wrapper.
				$wrapperRenderer = ($fieldReference->isReadOnly()) ?
						$this->getFactory()->createFieldWrapperReadOnlyRenderer($field) :
						$this->getFactory()->createFieldWrapperRenderer($field);
				$wrapperRenderer->render($output);
			} else {
				// isWrapped() is false, so just render the field.
				$this->getFactory()->createFieldRenderer($field, array())->render($output);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	protected function normaliseRenderOptions() {
		return ArrayUtilities::combine(
			parent::normaliseRenderOptions(),
			array(
				self::RENDER_OPTION_KEY_ELEMENT_NAME => 'fieldset'
			)
		);
	}

}
