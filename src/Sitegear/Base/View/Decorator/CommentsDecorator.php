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
use Sitegear\Util\TypeUtilities;

use Symfony\Component\HttpFoundation\Request;

/**
 * Decorates the given content with a comment at the beginning at the end, to assist with debugging / rendered page
 * readability.
 */
class CommentsDecorator implements DecoratorInterface {

	//-- Constants --------------------

	/**
	 * Default marker to use in the begin comment.
	 */
	const DEFAULT_BEGIN_MARKER = 'BEGIN';

	/**
	 * Default marker to use in the end comment.
	 */
	const DEFAULT_END_MARKER = 'END';

	//-- DecoratorInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function decorate($content, ViewInterface $view=null, $beginMarker=null, $endMarker=null) {
		$beginMarker = $beginMarker ?: self::DEFAULT_BEGIN_MARKER;
		$endMarker = $endMarker ?: self::DEFAULT_END_MARKER;
		// Only output the comments in development environments
		if (!$view->getEngine()->getEnvironmentInfo()->isDevMode()) {
			return $content;
		}
		// Determine the spec, which is printed at the beginning and end
		$spec = array( 'view' );
		for ($i=0, $len=$view->getTargetCount(); $i<$len; ++$i) {
			$spec[] = $view->getTarget($i);
		}
		$spec = implode('.', $spec);
		// Render and return the result
		return sprintf('<!-- %s [[ %s ]] --> %s%s<!-- %s [[ %s ]] -->%s', $beginMarker, $spec, PHP_EOL, $content, $endMarker, $spec, PHP_EOL);
	}

}
