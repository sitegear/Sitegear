<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer\Element;

use Sitegear\Base\Form\Renderer\Factory\FormRendererFactoryInterface;
use Sitegear\Base\Form\Element\ElementInterface;

abstract class AbstractElementRenderer implements ElementRendererInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Form\Element\ElementInterface
	 */
	private $element;

	/**
	 * @var \Sitegear\Base\Form\Renderer\Factory\FormRendererFactoryInterface
	 */
	private $factory;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\Form\Element\ElementInterface $element
	 * @param \Sitegear\Base\Form\Renderer\Factory\FormRendererFactoryInterface $factory
	 */
	public function __construct(ElementInterface $element, FormRendererFactoryInterface $factory) {
		$this->element = $element;
		$this->factory = $factory;
	}

	//-- ElementRendererInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getElement() {
		return $this->element;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFactory() {
		return $this->factory;
	}

	/**
	 * {@inheritDoc}
	 */
	public function render(array $options) {
		return array_merge(
			$this->startRendering($options),
			$this->renderChildren($options),
			$this->finishRendering($options)
		);
	}

	//-- Internal Methods --------------------

	/**
	 * Called before the element's children are rendered.  Render the start of the element.
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	protected function startRendering(array $options) {
		return array();
	}

	/**
	 * Render the children of the given element.
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	protected function renderChildren(array $options) {
		$result = array();
		foreach ($this->getElement()->getChildren() as $child) {
			$childRenderer = $this->getFactory()->getElementRenderer($child);
			$result = array_merge($result, $childRenderer->render($options));
		}
		return $result;
	}

	/**
	 * Called after the element's are completely rendered.  Render the end of the element.
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	protected function finishRendering(array $options) {
		return array();
	}

}
