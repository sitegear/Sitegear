<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Decorator;

use Symfony\Component\HttpFoundation\Request;

/**
 * Defines the ability to decorate content in some way.  Decorators can be applied at any rendering level, i.e.
 * templates, sections and components, and can modify the content string in any way.
 */
interface DecoratorInterface {

	/**
	 * Decorate the given content and return the result.
	 *
	 * @param string $content Content to render.
	 * @varargs Arguments for the decorator, as expected by the specific decorator implementation.  Can be passed the
	 *   view and the request using type hinting.
	 *
	 * @return string Decorated content.
	 */
	public function decorate($content);

}
