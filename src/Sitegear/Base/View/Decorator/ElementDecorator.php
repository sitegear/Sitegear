<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\View\Decorator;

use Sitegear\Util\HtmlUtilities;

use Symfony\Component\HttpFoundation\Request;

/**
 * Decorates the given content by wrapping it with a particular type of element (defined by arguments passed to the
 * constructor).
 */
class ElementDecorator implements DecoratorInterface {

	//-- DecoratorInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function decorate($content, $element=null, $attributes=null) {
		// Allow a default element if no arguments are passed (other than the content and view)
		$element = $element ?: $this->defaultElement();
		// Normalise the attributes
		if (is_array($attributes)) {
			$attributes = HtmlUtilities::attributes($attributes);
		} elseif (is_string($attributes) && strlen($attributes) > 0) {
			$attributes = ' ' . trim($attributes);
		} else {
			$attributes = '';
		}
		// Generate the final HTML
		return sprintf('<%s%s>%s%s</%s>%s', $element, $attributes, $this->textAfterOpenTag(), $content, $element, PHP_EOL);
	}

	/**
	 * Get the element name ot use when none is specified.
	 *
	 * @return string
	 */
	protected function defaultElement() {
		return 'div';
	}

	/**
	 * Get the text to insert after the element's opening tag.  Normally a newline but this can be overridden to use a
	 * different output format.
	 *
	 * @return string
	 */
	protected function textAfterOpenTag() {
		return PHP_EOL;
	}

}
