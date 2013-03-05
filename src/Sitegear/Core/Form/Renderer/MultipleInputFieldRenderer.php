<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Form\Renderer;

use Sitegear\Util\NameUtilities;
use Sitegear\Util\HtmlUtilities;

/**
 * Renderer for a `MultipleInputField`.
 *
 * @method \Sitegear\Base\Form\Field\MultipleInputField getField()
 */
class MultipleInputFieldRenderer extends AbstractFieldRenderer {

	//-- Constants --------------------

	const RENDER_OPTION_KEY_OUTER_WRAPPER_ELEMENT_NAME = 'outer-wrapper-element-name';

	const RENDER_OPTION_KEY_OUTER_WRAPPER_ATTRIBUTES = 'outer-wrapper-attributes';

	const RENDER_OPTION_KEY_INNER_WRAPPER_ELEMENT_NAME = 'inner-wrapper-element-name';

	const RENDER_OPTION_KEY_INNER_WRAPPER_ATTRIBUTES = 'inner-wrapper-attributes';

	const RENDER_OPTION_KEY_INNER_WRAPPER_ID_PREFIX = 'inner-wrapper-id-prefix';

	const RENDER_OPTION_KEY_LABELS_FIRST = 'labels-first';

	//-- RendererInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function render(array & $output) {
		$innerIdPrefix = sprintf(
			'%s-%s',
			$this->getField()->getName(), $this->getRenderOption(self::RENDER_OPTION_KEY_INNER_WRAPPER_ID_PREFIX)
		);
		$output[] = sprintf(
			'<%s%s>',
			$this->getRenderOption(self::RENDER_OPTION_KEY_OUTER_WRAPPER_ELEMENT_NAME),
			HtmlUtilities::attributes($this->getRenderOption(self::RENDER_OPTION_KEY_OUTER_WRAPPER_ATTRIBUTES))
		);
		foreach ($this->getField()->getValues() as $option) {
			$optionId = sprintf(
				'%s-%s',
				$innerIdPrefix,
				NameUtilities::convertToDashedLower($option['value'])
			);
			$optionAttributes = array_merge(
				$this->getRenderOption(self::RENDER_OPTION_KEY_ATTRIBUTES),
				array(
					'id' => $optionId,
					'value' => $option['value']
				)
			);
			if ($option['value'] === $this->getField()->getValue()) {
				$optionAttributes['selected'] = 'selected';
			}
			$input = sprintf(
				'<input%s />',
				HtmlUtilities::attributes($optionAttributes)
			);
			$inputLabelAttributes = array(
				'for' => $optionId,
				'class' => sprintf('multi-input-label %s-label', $this->getField()->getType())
			);
			$inputLabel = sprintf(
				'<label%s>%s</label>',
				HtmlUtilities::attributes($inputLabelAttributes),
				$option['label']
			);
			$optionWrapperAttributes = $this->getRenderOption(self::RENDER_OPTION_KEY_INNER_WRAPPER_ATTRIBUTES);
			$optionWrapperAttributes['id'] = sprintf(
				'%s-container',
				$innerIdPrefix
			);
			$output[] = sprintf(
				'<%s%s>',
				$this->getRenderOption(self::RENDER_OPTION_KEY_INNER_WRAPPER_ELEMENT_NAME),
				HtmlUtilities::attributes($optionWrapperAttributes)
			);
			if ($this->getRenderOption(self::RENDER_OPTION_KEY_LABELS_FIRST)) {
				$output[] = $inputLabel;
				$output[] = $input;
			} else {
				$output[] = $input;
				$output[] = $inputLabel;
			}
			$output[] = sprintf(
				'</%s>',
				$this->getRenderOption(self::RENDER_OPTION_KEY_INNER_WRAPPER_ELEMENT_NAME)
			);
		}
		$output[] = sprintf(
			'</%s>',
			$this->getRenderOption(self::RENDER_OPTION_KEY_OUTER_WRAPPER_ELEMENT_NAME)
		);
	}

	//-- AbstractInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function normaliseRenderOptions(array $renderOptions=null) {
		// TODO Handle "class" attributes properly
		$renderOptions = parent::normaliseRenderOptions($renderOptions);
		// Input elements settings
		$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['type'] = $this->getField()->getType();
		$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['name'] = $this->getField()->getName();
		$extraClass = $this->getField()->getType();
		if (isset($renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['class'])) {
			$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['class'] = sprintf('%s %s', $renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['class'], $extraClass);
		} else {
			$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['class'] = $extraClass;
		}
		// Outer wrapper settings
		if (!isset($renderOptions[self::RENDER_OPTION_KEY_OUTER_WRAPPER_ELEMENT_NAME])) {
			$renderOptions[self::RENDER_OPTION_KEY_OUTER_WRAPPER_ELEMENT_NAME] = 'ul';
		}
		if (!isset($renderOptions[self::RENDER_OPTION_KEY_OUTER_WRAPPER_ATTRIBUTES])) {
			$renderOptions[self::RENDER_OPTION_KEY_OUTER_WRAPPER_ATTRIBUTES] = array();
		}
		if (!isset($renderOptions[self::RENDER_OPTION_KEY_OUTER_WRAPPER_ATTRIBUTES]['id'])) {
			$renderOptions[self::RENDER_OPTION_KEY_OUTER_WRAPPER_ATTRIBUTES]['id'] = sprintf('%s-container', $this->getField()->getName());
		}
		$extraOuterWrapperClass = sprintf('multi-input-container %s-container', $this->getField()->getType());
		if (isset($renderOptions[self::RENDER_OPTION_KEY_OUTER_WRAPPER_ATTRIBUTES]['class'])) {
			$outerWrapperClass = sprintf('%s %s', $renderOptions[self::RENDER_OPTION_KEY_OUTER_WRAPPER_ATTRIBUTES]['class'], $extraOuterWrapperClass);
		} else {
			$outerWrapperClass = $extraOuterWrapperClass;
		}
		$renderOptions[self::RENDER_OPTION_KEY_OUTER_WRAPPER_ATTRIBUTES]['class'] = $outerWrapperClass;
		// Inner wrapper settings
		if (!isset($renderOptions[self::RENDER_OPTION_KEY_INNER_WRAPPER_ELEMENT_NAME])) {
			$renderOptions[self::RENDER_OPTION_KEY_INNER_WRAPPER_ELEMENT_NAME] = 'li';
		}
		if (!isset($renderOptions[self::RENDER_OPTION_KEY_INNER_WRAPPER_ATTRIBUTES])) {
			$renderOptions[self::RENDER_OPTION_KEY_INNER_WRAPPER_ATTRIBUTES] = array();
		}
		if (!isset($renderOptions[self::RENDER_OPTION_KEY_INNER_WRAPPER_ID_PREFIX])) {
			$renderOptions[self::RENDER_OPTION_KEY_INNER_WRAPPER_ID_PREFIX] = 'value';
		}
		// Set label elements first or input elements first
		if (!isset($renderOptions[self::RENDER_OPTION_KEY_LABELS_FIRST])) {
			$renderOptions[self::RENDER_OPTION_KEY_LABELS_FIRST] = false;
		}
		return $renderOptions;
	}

}
