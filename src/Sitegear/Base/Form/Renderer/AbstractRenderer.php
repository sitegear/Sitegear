<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer;

/**
 * Abstract base renderer class.
 */
abstract class AbstractRenderer implements RendererInterface {

	//-- Constants --------------------

	const RENDER_OPTION_KEY_ELEMENT_NAME = 'element';

	const RENDER_OPTION_KEY_ATTRIBUTES = 'attributes';

	const RENDER_OPTION_KEY_VALUE = 'value';

	//-- Attributes --------------------

	/**
	 * @var array
	 */
	private $renderOptions;

	//-- Constructor --------------------

	/**
	 * @param array|null $renderOptions
	 */
	public function __construct(array $renderOptions=null) {
		$this->renderOptions = $this->normaliseRenderOptions($renderOptions);
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

	/**
	 * This method should be overridden by subclasses wishing to extend the normalising behaviour (e.g. ensure the
	 * existence of additional keys, etc).  Overriding implementations should be sure to call this implementation to
	 * provide a baseline.
	 *
	 * @param array|null $renderOptions
	 *
	 * @return array
	 */
	protected function normaliseRenderOptions(array $renderOptions=null) {
		if (is_null($renderOptions)) {
			$renderOptions = array();
		}
		if (!isset($renderOptions[self::RENDER_OPTION_KEY_ELEMENT_NAME])) {
			$renderOptions[self::RENDER_OPTION_KEY_ELEMENT_NAME] = 'div';
		}
		if (!isset($renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES])) {
			$renderOptions[self::RENDER_OPTION_KEY_ATTRIBUTES] = array();
		}
		return $renderOptions;
	}

}
