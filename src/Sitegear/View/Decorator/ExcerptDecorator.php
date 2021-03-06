<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Decorator;

use Sitegear\View\ViewInterface;
use Sitegear\Util\HtmlUtilities;

use Symfony\Component\HttpFoundation\Request;

class ExcerptDecorator implements DecoratorInterface {

	//-- DecoratorInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function decorate($content, $length=null, $default=null) {
		if (is_numeric($length)) {
			$content = HtmlUtilities::excerpt(!empty($content) ? $content : $default, intval($length)) . PHP_EOL;
		}
		return $content;
	}

}
