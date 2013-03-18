<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Form\Renderer;

use Sitegear\Base\Form\Renderer\Factory\RendererFactoryInterface;
use Sitegear\Base\Form\Renderer\RendererInterface;

/**
 * Abstract base renderer class.
 */
abstract class AbstractRenderer implements RendererInterface {

	//-- Constants --------------------

	/**
	 * Render option key used to specify the element name.
	 */
	const RENDER_OPTION_KEY_ELEMENT_NAME = 'element';

	/**
	 * Render option key used to specify a key-value array of attributes.
	 */
	const RENDER_OPTION_KEY_ATTRIBUTES = 'attributes';

	//-- Attributes --------------------

	/**
	 * @var array
	 */
	private $renderOptions;

	//-- Constructor --------------------

	/**
	 * @param RendererFactoryInterface $factory
	 */
	public function __construct(RendererFactoryInterface $factory) {
		$this->factory = $factory;
		$this->renderOptions = $this->normaliseRenderOptions();
	}

	//-- RendererInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getRenderOption($key) {
		return isset($this->renderOptions[$key]) ? $this->renderOptions[$key] : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setRenderOption($key, $value) {
		$this->renderOptions[$key] = $value;
	}

	//-- Internal Methods --------------------

	protected function getFactory() {
		return $this->factory;
	}

	/**
	 * This method should be overridden by subclasses wishing to extend the normalising behaviour (e.g. ensure the
	 * existence of additional keys, etc).  Overriding implementations should be sure to call this implementation to
	 * provide a baseline.
	 *
	 * @return array
	 */
	protected function normaliseRenderOptions() {
		$renderOptions = $this->getFactory()->getRenderOptionDefaults(get_class($this));
		if (!isset($renderOptions[self::RENDER_OPTION_KEY_ELEMENT_NAME])) {
			$renderOptions[self::RENDER_OPTION_KEY_ELEMENT_NAME] = 'div';
		}
		if (!isset($renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES])) {
			$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES] = array();
		}
		return $renderOptions;
	}

}
