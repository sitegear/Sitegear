<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\View\Decorator;

/**
 * Decorator that doesn't modify the content at all.  This can be used for testing but also to disable specific
 * decorators supplied by the default config, by overriding the decorator key with this class (use with care!).
 */
class NoOpDecorator implements DecoratorInterface {

	/**
	 * @inheritdoc
	 */
	public function decorate($content) {
		return $content;
	}

}
