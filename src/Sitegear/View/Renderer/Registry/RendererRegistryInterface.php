<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Renderer\Registry;

use Sitegear\View\ViewInterface;

/**
 * Defines the behaviour of a registry for renderer implementations, which allows a given view to be rendered by the
 * first registered renderer that supports that view.
 */
interface RendererRegistryInterface {

	//-- Registration Methods --------------------

	/**
	 * Register the given content renderer, if it is not already registered.
	 *
	 * @param string|\Sitegear\View\Renderer\RendererInterface|array $renderers Class name of a RendererInterface
	 *   implementation to register, or an object implementing RendererInterface, or an array of strings or objects.
	 *
	 * @throws \InvalidArgumentException If the specified class name does not exist or does not implement
	 *   \Sitegear\View\Renderer\RendererInterface.
	 */
	public function register($renderers);

	/**
	 * Deregister the given content renderer, if it is registered.
	 *
	 * @param string|\Sitegear\View\Renderer\RendererInterface $renderers Class name of a RendererInterface implementation
	 *   to deregister, or an object implementing RendererInterface, or an array of strings or objects.
	 */
	public function deregister($renderers);

	/**
	 * Determine if the given (single) content renderer is registered.
	 *
	 * @param string|\Sitegear\View\Renderer\RendererInterface $renderer Class name to look for in the registry, or an
	 *   object implementing RendererInterface.  May be anything but only valid, matching class names or instances will
	 *   result in a return value of true.
	 *
	 * @return boolean Whether or not the given class is registered.
	 */
	public function isRegistered($renderer);

	//-- Rendering Methods --------------------

	/**
	 * Determine if the given view can be successfully rendered.
	 *
	 * @param string $path Script path to find a matching renderer for.
	 *
	 * @return boolean Whether or not the view script at the given path can be rendered.
	 */
	public function canRender($path);

	/**
	 * Render the given view, with the first registered renderer that supports it.
	 *
	 * @param string $path Script path to find a matching renderer for.
	 * @param \Sitegear\View\ViewInterface $view Rendering context.
	 *
	 * @return string|null Rendered content returned by the RendererInterface implementation, or null if no renderer
	 *   could be found.
	 */
	public function render($path, ViewInterface $view);

}
