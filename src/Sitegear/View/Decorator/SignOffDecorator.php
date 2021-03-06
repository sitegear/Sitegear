<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Decorator;

use Sitegear\View\ViewInterface;
use Sitegear\View\Decorator\DecoratorInterface;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;

use Monolog\Logger;

/**
 * Decorator that adds a HTML comment and a log message.
 *
 * Intended to be used at the template level only.
 */
class SignOffDecorator implements DecoratorInterface {

	//-- Decorator Interface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function decorate($content, ViewInterface $view=null, Request $request=null) {
		$renderTime = $this->formatTime($view->getEngine()->getTimestamp());
		$version = $view->getEngine()->getApplicationInfo()->getSitegearVersionIdentifier();
		$userManager = $view->getEngine()->getUserManager();
		$comment = sprintf(
			'<!-- %s%s :: %s :: %s -->%s<!-- %s :: %s :: %s -->',
			$request->getUri(),
			is_null($view->getEngine()->getEnvironmentInfo()->getEnvironment()) ? '' : sprintf(' :: %s environment', $view->getEngine()->getEnvironmentInfo()->getEnvironment()),
			$userManager->isLoggedIn() ? 'logged in as ' . ($userManager->getLoggedInUserEmail() ?: 'guest') : 'not logged in',
			$renderTime,
			PHP_EOL,
			$view->getEngine()->getApplicationInfo()->getSitegearHomepage(),
			$version,
			$this->formatNow()
		);
		LoggerRegistry::log($this->logLevel(), '{pathInfo} {renderTime} by {version}', array( 'pathInfo' => $request->getPathInfo(), 'renderTime' => $renderTime, 'version' => $version ));
		return $content . $comment . PHP_EOL;
	}

	//-- Internal Methods --------------------

	protected function formatTime($start) {
		return sprintf('rendered in %.5fms', (microtime(true) - $start) * 1000);
	}

	protected function formatNow() {
		return date('Y-m-d H:i:s T');
	}

	protected function logLevel() {
		return Logger::INFO;
	}

}
