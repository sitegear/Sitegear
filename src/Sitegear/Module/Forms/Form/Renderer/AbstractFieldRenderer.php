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
use Sitegear\Base\Form\Renderer\FieldRendererInterface;
use Sitegear\Module\Forms\Form\Renderer\AbstractRenderer;
use Sitegear\Util\ArrayUtilities;

/**
 * Abstract implementation of `FieldRendererInterface`.  Implements storage of and access to the related field object
 * and the factory object responsible for generation of renderers.
 */
abstract class AbstractFieldRenderer extends AbstractRenderer implements FieldRendererInterface {

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

	//-- FieldRendererInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getField() {
		return $this->field;
	}

	//-- AbstractInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function normaliseRenderOptions() {
		return ArrayUtilities::combine(
			array(
				self::RENDER_OPTION_KEY_ATTRIBUTES => array(
					'id' => $this->getField()->getName()
				)
			),
			ArrayUtilities::combine(
				parent::normaliseRenderOptions(),
				array(
					self::RENDER_OPTION_KEY_ATTRIBUTES => array(
						'name' => $this->getFieldNameAttribute()
					)
				)
			)
		);
	}

	//-- Internal Methods --------------------

	/**
	 * Shortcut method to get the value set for this renderer's related field.
	 *
	 * @return mixed|null
	 */
	protected function getFieldValue() {
		return $this->getField()->getForm()->getFieldValue($this->getField()->getName(), $this->getField()->getDefaultValue());
	}

	/**
	 * Retrieve the field's name attribute value, which is the field name plus [] if it is an array value field.
	 *
	 * @return string
	 */
	protected function getFieldNameAttribute() {
		$name = $this->getField()->getName();
		if ($this->getField()->isArrayValue()) {
			$name .= '[]';
		}
		return $name;
	}

}
