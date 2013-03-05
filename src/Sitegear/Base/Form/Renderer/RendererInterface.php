<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer;

/**
 * Defines the basic behaviour common to all types of renderers.
 */
interface RendererInterface {

	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getRenderOption($key);

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function setRenderOption($key, $value);

	/**
	 * @param string[] $output
	 */
	public function render(array & $output);

}
