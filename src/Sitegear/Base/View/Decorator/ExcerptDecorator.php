<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\View\Decorator;

use Sitegear\Base\View\ViewInterface;
use Sitegear\Util\HtmlUtilities;

use Symfony\Component\HttpFoundation\Request;

class ExcerptDecorator implements DecoratorInterface {

	//-- DecoratorInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function decorate($content, $length=null) {
		if (is_numeric($length)) {
			$content = HtmlUtilities::excerpt($content, intval($length)) . PHP_EOL;
		}
		return $content;
	}

}
