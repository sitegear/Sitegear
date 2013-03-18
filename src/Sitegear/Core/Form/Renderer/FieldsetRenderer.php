<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Form\Renderer;

use Sitegear\Base\Form\FieldsetInterface;
use Sitegear\Core\Form\Renderer\Factory\FieldRendererFactory;

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
	 * @param \Sitegear\Base\Form\FieldsetInterface $fieldset
	 * @param string[]|null $renderOptions
	 */
	public function __construct(FieldsetInterface $fieldset, array $renderOptions=null) {
		$this->fieldset = $fieldset;
		parent::__construct($renderOptions);
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
	 *
	 * TODO Pass field wrapper render options
	 */
	protected function renderChildren(array & $output) {
		$form = $this->getFieldset()->getStep()->getForm();
		$fieldRendererFactory = new FieldRendererFactory();
		foreach ($this->getFieldset()->getFieldReferences() as $fieldReference) {
			$field = $form->getField($fieldReference->getFieldName());
			if ($fieldReference->isWrapped()) {
				$wrapperRenderer = ($fieldReference->isReadOnly()) ?
						new FieldWrapperReadOnlyRenderer($field) :
						new FieldWrapperRenderer($field);
				$wrapperRenderer->render($output);
			} else {
				$fieldRendererFactory->getFieldRenderer($field, array())->render($output);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	protected function normaliseRenderOptions(array $renderOptions=null) {
		$renderOptions = parent::normaliseRenderOptions($renderOptions);
		$renderOptions[self::RENDER_OPTION_KEY_ELEMENT_NAME] = 'fieldset';
		return $renderOptions;
	}

}
