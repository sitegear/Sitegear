<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Form\Renderer;

/**
 * Defines the basic behaviour common to all types of renderers.
 */
interface RendererInterface {

	/**
	 * Retrieve the rendering option with the given key.
	 *
	 * @param string $key
	 * @param mixed|null $default
	 *
	 * @return mixed
	 */
	public function getRenderOption($key, $default=null);

	/**
	 * Change the rendering option with the given key.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function setRenderOption($key, $value);

	/**
	 * Render to the given output array; each element in the array is a line of output.
	 *
	 * @param string[] $output
	 */
	public function render(array & $output);

}
