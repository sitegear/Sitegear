<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Engine\KernelEvent;

use Sitegear\Util\TypeUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Responds to the REQUEST kernel event, linking to the engine's life cycle methods.
 */
class EngineBootstrapListener extends AbstractEngineKernelListener {

	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents() {
		return array(
			KernelEvents::REQUEST => array( 'onKernelRequest', 2048 )
		);
	}

	/**
	 * Start the engine and bootstrap the modules specified by the engine's bootstrap sequence.
	 *
	 * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
	 */
	public function onKernelRequest(GetResponseEvent $event) {
		LoggerRegistry::debug('EngineBootstrapListener responding to REQUEST kernel event');
		// Start the engine.
		$request = $event->getRequest(); /** @var \Symfony\Component\HttpFoundation\Request $request */
		$response = $this->getEngine()->start($request);
		if (!is_null($response)) {
			// Set the response directly; prevent further processing.
			$this->getEngine()->instrumentResponse($response);
			$event->setResponse($response);
			$event->stopPropagation();
			LoggerRegistry::debug(sprintf('EngineBootstrapListener received Response [%s] from bootstrap', TypeUtilities::describe($response)));
		}
	}

}
