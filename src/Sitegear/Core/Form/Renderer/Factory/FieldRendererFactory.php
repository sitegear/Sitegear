<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Form\Renderer\Factory;

use Sitegear\Util\TypeUtilities;
use Sitegear\Base\Form\Field\FieldInterface;

/**
 * Factory for generating a renderer based on a given field type.
 */
class FieldRendererFactory {

	/**
	 * Create a field renderer for this field wrapper's field type.
	 *
	 * @param \Sitegear\Base\Form\Field\FieldInterface $field
	 * @param array $renderOptions
	 *
	 * @return \Sitegear\Base\Form\Renderer\Field\FieldRendererInterface
	 *
	 * TODO Allow different namespaces??
	 */
	public function getFieldRenderer(FieldInterface $field, array $renderOptions=null) {
		$fieldClass = new \ReflectionClass($field);
		$fieldRendererClassName = sprintf('\\Sitegear\\Core\\Form\\Renderer\\Field\\%sRenderer', $fieldClass->getShortName());
		return TypeUtilities::buildTypeCheckedObject(
			$fieldRendererClassName,
			'field renderer',
			null,
			array(
				'\\Sitegear\\Base\\Form\\Renderer\\Field\\FieldRendererInterface'
			),
			array(
				$field,
				$renderOptions ?: array()
			)
		);
	}

}
