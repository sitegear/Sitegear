<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer\Field;

use Sitegear\Util\HtmlUtilities;

/**
 * @method \Sitegear\Base\Form\Field\MultipleInputField getField()
 */
class MultipleInputFieldRenderer extends AbstractFieldRenderer {

	/**
	 * {@inheritDoc}
	 */
	public function render(array $options, $value) {
		$attributes = isset($options['attributes']) ? $options['attributes'] : array();
		$outerWrapper = isset($options['outer-wrapper']) ? $options['outer-wrapper'] : array();
		$outerWrapperElement = isset($outerWrapper['element']) ? $outerWrapper['element'] : 'ul';
		$outerWrapperAttributes = isset($outerWrapper['attributes']) ? $outerWrapper['attributes'] : array();
		$outerWrapperAttributes['id'] = isset($outerWrapperAttributes['id']) ? $outerWrapperAttributes['id'] : $this->getField()->getName() . '-container';
		$extraOuterWrapperClass = sprintf('multi-input-container %s-container', $this->getField()->getType());
		$outerWrapperAttributes['class'] = (isset($outerWrapperAttributes['class']) ? $outerWrapperAttributes['class'] . ' ' : '') . $extraOuterWrapperClass;
		$innerWrapper = isset($options['inner-wrapper']) ? $options['inner-wrapper'] : array();
		$innerWrapperElement = isset($innerWrapper['element']) ? $innerWrapper['element'] : 'li';
		$innerWrapperAttributes = isset($innerWrapper['attributes']) ? $innerWrapper['attributes'] : array();
		$innerIdPrefix = sprintf('%s-%s', $this->getField()->getName(), isset($innerWrapper['id-prefix']) ? $innerWrapper['id-prefix'] : 'value');
		$labelFirst = isset($options['label-first']) && $options['label-first'];
		$result = array();
		$result[] = sprintf('<%s%s>', $outerWrapperElement, HtmlUtilities::attributes($outerWrapperAttributes));
		$result[] = sprintf('<input type="hidden" name="%s" value="%s" />', $this->getField()->getName(), $value);
		foreach ($this->getField()->getValues() as $option) {
			$optionId = sprintf('%s-%s', $innerIdPrefix, preg_replace('/\s+/', '', $option['value']));
			$optionAttributes = $attributes;
			$optionAttributes['type'] = $this->getField()->getType();
			$optionAttributes['id'] = $optionId;
			$optionAttributes['name'] = $this->getField()->getName();
			$optionAttributes['value'] = $option['value'];
			$optionAttributes['class'] = (isset($optionAttributes['class']) ? $optionAttributes['class'] . ' ' : '') . $this->getField()->getType();
			if ($option['value'] === $value) {
				$optionAttributes['selected'] = 'selected';
			}
			$input = sprintf('<input%s />', HtmlUtilities::attributes($optionAttributes));
			$inputLabel = sprintf(
				'<label for="%s" class="multi-input-label %s-label">%s</label>',
				$optionId,
				$this->getField()->getType(),
				$option['label']
			);
			$optionWrapperAttributes = $innerWrapperAttributes;
			$optionWrapperAttributes['id'] = sprintf('%s-container', $innerIdPrefix);
			$result[] = sprintf('<%s%s>', $innerWrapperElement, HtmlUtilities::attributes($optionWrapperAttributes));
			if ($labelFirst) {
				$result[] = $inputLabel;
				$result[] = $input;
			} else {
				$result[] = $input;
				$result[] = $inputLabel;
			}
			$result[] = sprintf('</%s>', $innerWrapperElement);
		}
		$result[] = sprintf('</%s>', $outerWrapperElement);
		return $result;
	}

}
