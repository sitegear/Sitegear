<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Forms\Form\Renderer;

use Sitegear\Form\FieldsetInterface;
use Sitegear\Form\Renderer\Factory\RendererFactoryInterface;
use Sitegear\Module\Forms\Form\Renderer\AbstractContainerRenderer;
use Sitegear\Util\ArrayUtilities;
use Sitegear\Util\NameUtilities;

/**
 * RendererInterface implementation for a fieldset.
 */
class FieldsetRenderer extends AbstractContainerRenderer {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Form\FieldsetInterface
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
	 * @return \Sitegear\Form\FieldsetInterface
	 */
	public function getFieldset() {
		return $this->fieldset;
	}

	//-- AbstractRenderer Methods --------------------

	/**
	 * @inheritdoc
	 */
	protected function renderChildren(array & $output) {
		$form = $this->getFieldset()->getStep()->getForm();
		if (!is_null($this->getFieldset()->getHeading())) {
			$output[] = sprintf('<legend>%s</legend>', $this->getFieldset()->getHeading());
		}
		foreach ($this->getFieldset()->getFieldReferences() as $fieldReference) {
			$field = $form->getField($fieldReference->getFieldName());
			if ($field->shouldBeIncluded($this->getFieldset()->getStep()->getForm()->getValues())) {
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
	}

	/**
	 * @inheritdoc
	 */
	protected function normaliseRenderOptions() {
		return ArrayUtilities::combine(
			parent::normaliseRenderOptions(),
			array(
				self::RENDER_OPTION_KEY_ELEMENT_NAME => 'fieldset',
				self::RENDER_OPTION_KEY_ATTRIBUTES => array(
					'id' => sprintf('sitegear-fieldset-%s', NameUtilities::convertToDashedLower($this->getFieldset()->getHeading()))
				)
			)
		);
	}

}
