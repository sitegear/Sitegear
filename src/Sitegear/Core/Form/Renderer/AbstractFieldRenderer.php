<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Form\Renderer;

use Sitegear\Base\Form\Field\FieldInterface;
use Sitegear\Base\Form\Renderer\Factory\RendererFactoryInterface;
use Sitegear\Base\Form\Renderer\FieldRendererInterface;
use Sitegear\Core\Form\Renderer\AbstractRenderer;

/**
 * Abstract implementation of `FieldRendererInterface`.  Implements storage of and access to the related field object
 * and the factory object responsible for generation of renderers.
 */
abstract class AbstractFieldRenderer extends AbstractRenderer implements FieldRendererInterface {

	//-- Constants --------------------

	/**
	 * Render option key used to specify the current value(s) of the element being rendered.
	 */
	const RENDER_OPTION_KEY_VALUE = 'value';

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
		$renderOptions = parent::normaliseRenderOptions();
		if (!isset($renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['id'])) {
			$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['id'] = $this->getField()->getName();
		}
		$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES]['name'] = $this->getField()->getName();
		$renderOptions[self::RENDER_OPTION_KEY_VALUE] = $this->getField()->getValue();
		return $renderOptions;
	}

}
