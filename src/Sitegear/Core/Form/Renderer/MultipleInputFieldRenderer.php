<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Form\Renderer;

use Sitegear\Util\NameUtilities;
use Sitegear\Util\ArrayUtilities;
use Sitegear\Util\HtmlUtilities;

/**
 * Renderer for a `MultipleInputField`.
 *
 * @method \Sitegear\Base\Form\Field\MultipleInputField getField()
 */
class MultipleInputFieldRenderer extends AbstractFieldRenderer {

	//-- Constants --------------------

	/**
	 * Render option key used to specify the outer wrapper element name.
	 */
	const RENDER_OPTION_KEY_OUTER_WRAPPER_ELEMENT_NAME = 'outer-wrapper-element-name';

	/**
	 * Render option key used to specify the outer wrapper attributes.
	 */
	const RENDER_OPTION_KEY_OUTER_WRAPPER_ATTRIBUTES = 'outer-wrapper-attributes';

	/**
	 * Render option key used to specify the inner wrapper element name.
	 */
	const RENDER_OPTION_KEY_INNER_WRAPPER_ELEMENT_NAME = 'inner-wrapper-element-name';

	/**
	 * Render option key used to specify the inner wrapper attributes.
	 */
	const RENDER_OPTION_KEY_INNER_WRAPPER_ATTRIBUTES = 'inner-wrapper-attributes';

	/**
	 * Render option key used to specify the ID attribute value prefix for inner wrapper elements.
	 */
	const RENDER_OPTION_KEY_INNER_WRAPPER_ID_PREFIX = 'inner-wrapper-id-prefix';

	/**
	 * Render option key used to determine whether the value labels should be shown before (true) or after (false) the
	 * corresponding input element.
	 */
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
	protected function normaliseRenderOptions() {
		return ArrayUtilities::combine(
			array(
				self::RENDER_OPTION_KEY_OUTER_WRAPPER_ELEMENT_NAME => 'div',
				self::RENDER_OPTION_KEY_OUTER_WRAPPER_ATTRIBUTES => array(
					'id' => sprintf('%s-container', $this->getField()->getName()),
					'class' => sprintf('multi-input-container %s-container', $this->getField()->getType())
				),
				self::RENDER_OPTION_KEY_INNER_WRAPPER_ELEMENT_NAME => 'div',
				self::RENDER_OPTION_KEY_INNER_WRAPPER_ATTRIBUTES => array(),
				self::RENDER_OPTION_KEY_INNER_WRAPPER_ID_PREFIX => 'value',
				self::RENDER_OPTION_KEY_LABELS_FIRST => false
			),
			parent::normaliseRenderOptions(),
			array(
				self::RENDER_OPTION_KEY_ATTRIBUTES => array(
					'type' => $this->getField()->getType(),
					'name' => $this->getField()->getName(),
					'class' => $this->getField()->getType()
				)
			)
		);
	}

}
