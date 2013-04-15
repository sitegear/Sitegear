<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\View\Decorator;

use Sitegear\Base\View\Decorator\DecoratorInterface;
use Sitegear\Base\View\ViewInterface;

use Symfony\Component\HttpFoundation\Request;

/**
 * Decorator that replaces tokens in the received content, with content generated by a resources manager.  In this way,
 * the tokens can be written at any time (e.g. in the <head> for stylesheets) and when the token is replaced after the
 * complete rendering cycle, it will include all resources including those that are registered after the token is
 * written.
 *
 * Intended to be used at the template level only.
 */
class ResourceTokensDecorator implements DecoratorInterface {

	//-- DecoratorInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function decorate($content, ViewInterface $view=null) {
		$registry = $view->getEngine()->getViewFactory()->getResourcesManager();
		foreach ($registry->types() as $type) {
			$content = preg_replace(self::pattern($type), $registry->render($type), $content);
		}
		return $content;
	}

	//-- Utility Methods --------------------

	/**
	 * Retrieve a token that can later be replaced by a rendering of all resources of the given type, using the
	 * pattern() method.
	 *
	 * @param string $type
	 *
	 * @return string Token, which is in the form of a HTML comment in case a problem occurs and the token is never
	 *   replaced.
	 */
	public static function token($type) {
		return sprintf('<!--[[ sitegear.resources.%s ]]-->%s', $type, PHP_EOL);
	}

	/**
	 * Get the regular expression that can be used to find and replace tokens generated by the token() method.
	 *
	 * @param string $type
	 *
	 * @return string Regular expression pattern, including delimiters.
	 */
	public static function pattern($type) {
		return sprintf('/<\\!\\-\\-\\[\\[\s*sitegear\\.resources\\.%s\s*\\]\\]\\-\\->\\n/', $type);
	}

}