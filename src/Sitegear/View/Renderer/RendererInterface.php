<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Renderer;

use Sitegear\View\ViewInterface;

/**
 * Interface that must be implemented by content renderers, that is, classes that are responsible for converting a view
 * script path to rendered output.
 */
interface RendererInterface {

	/**
	 * Determine whether this content renderer supports the given view.
	 *
	 * @param string $path View script path.
	 *
	 * @return boolean True if the given view script can be processed by render(), otherwise false.
	 */
	public function supports($path);

	/**
	 * Render the given view script and return the rendered content.  If output buffering is desired, this should
	 * occur here.  This should always render the site-specific content (if available) in preference to module internal
	 * content.
	 *
	 * @param string $path View script path.
	 * @param \Sitegear\View\ViewInterface $view Rendering context.
	 *
	 * @return string Rendered content.
	 *
	 * @throws \InvalidArgumentException If the given path is not supported by this renderer (i.e. supports() returns
	 *   false with the same arguments).
	 */
	public function render($path, ViewInterface $view);

}
