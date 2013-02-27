<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer\Field;

use Sitegear\Base\Form\Field\FieldInterface;
use Sitegear\Base\Form\Renderer\Factory\FormRendererFactoryInterface;

/**
 * Abstract implementation of `FieldRendererInterface`.  Implements storage of and access to the related field object
 * and the factory object responsible for generation of renderers.
 */
abstract class AbstractFieldRenderer implements FieldRendererInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Form\Field\FieldInterface
	 */
	private $field;

	/**
	 * @var \Sitegear\Base\Form\Renderer\Factory\FormRendererFactoryInterface
	 */
	private $factory;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\Form\Field\FieldInterface $field
	 * @param \Sitegear\Base\Form\Renderer\Factory\FormRendererFactoryInterface $factory
	 */
	public function __construct(FieldInterface $field, FormRendererFactoryInterface $factory) {
		$this->field = $field;
		$this->factory = $factory;
	}

	//-- FieldRendererInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getField() {
		return $this->field;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFactory() {
		return $this->factory;
	}

}
