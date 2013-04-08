<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\View\Decorator;

/**
 * Element decorator that uses inline tags (i.e. output is all on one line).
 */
class InlineElementDecorator extends ElementDecorator {

	/**
	 * @inheritdoc
	 */
	protected function defaultElement() {
		return 'span';
	}

	/**
	 * @inheritdoc
	 */
	protected function textAfterOpenTag() {
		return '';
	}

}