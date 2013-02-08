<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\View\Decorator;

use Sitegear\Base\View\Decorator\ElementDecorator;

use Symfony\Component\HttpFoundation\Request;

/**
 * Decorates the given content by wrapping it with an element.  This is used to hook up the client-side content
 * management controls.
 */
class EditableDecorator extends ElementDecorator {

	/**
	 * {@inheritDoc}
	 */
	public function decorate($content, $element=null, $class=null) {
		return parent::decorate($content, $element, array( 'class' => $class ?: 'sitegear-editable' ));
	}

}
