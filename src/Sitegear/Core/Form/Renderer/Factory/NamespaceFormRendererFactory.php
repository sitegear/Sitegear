<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Form\Renderer\Factory;

use Sitegear\Base\Form\Element\ElementInterface;
use Sitegear\Base\Form\Field\FieldInterface;
use Sitegear\Base\Form\Renderer\Factory\FormRendererFactoryInterface;

/**
 * Implementation of FormRendererFactoryInterface which uses namespace convention to determine the relevant renderers
 * for the supplied elements and fields.
 */
class NamespaceFormRendererFactory implements FormRendererFactoryInterface {

	//-- Constants --------------------

	/**
	 * Class name format for element renderer implementations.  The token is replaced by the short name of the element
	 * class.
	 */
	const CLASS_NAME_FORMAT_ELEMENT_RENDERER = '\\Sitegear\\Base\\Form\\Renderer\\Element\\%sRenderer';

	/**
	 * Class name format for field renderer implementations.  The token is replaced by the short name of the field
	 * class.
	 */
	const CLASS_NAME_FORMAT_FIELD_RENDERER = '\\Sitegear\\Base\\Form\\Renderer\\Field\\%sRenderer';

	//-- FormRendererFactoryInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getElementRenderer(ElementInterface $element) {
		$elementClass = new \ReflectionClass($element);
		$className = sprintf(self::CLASS_NAME_FORMAT_ELEMENT_RENDERER, $elementClass->getShortName());
		$rendererClass = new \ReflectionClass($className);
		return $rendererClass->newInstance($element, $this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFieldRenderer(FieldInterface $field) {
		$elementClass = new \ReflectionClass($field);
		$className = sprintf(self::CLASS_NAME_FORMAT_FIELD_RENDERER, $elementClass->getShortName());
		$rendererClass = new \ReflectionClass($className);
		return $rendererClass->newInstance($field, $this);
	}

}
