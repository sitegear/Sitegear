<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\View\Context;

use Sitegear\Base\View\Renderer\Registry\RendererRegistryInterface;
use Sitegear\Base\View\ViewInterface;

use Symfony\Component\HttpFoundation\Request;

/**
 * Defines the behaviour of a context for views.
 */
interface ViewContextInterface {

	/**
	 * Retrieve the method, closure, callable object or function name, that may be used to prepare the view data for
	 * the context's target, for the given request.
	 *
	 * The target controller can optionally accept the view object or the request object by using type-hinting to
	 * declare arguments of type \Sitegear\Base\View\ViewInterface and \Symfony\Component\HttpFoundation\Request.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return null|array|callable Either null, to represent that the view should simply be rendered, or an array,
	 *   closure or Callable object, or a function name.
	 */
	public function getTargetController(ViewInterface $view, Request $request);

	/**
	 * Find the best path within any location root where the path specified may be found within this context, and
	 * render it.
	 *
	 * @param \Sitegear\Base\View\Renderer\Registry\RendererRegistryInterface $rendererRegistry
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param mixed $methodResult
	 *
	 * @return string Rendered content.
	 */
	public function render(RendererRegistryInterface $rendererRegistry, ViewInterface $view, Request $request, $methodResult);

}
