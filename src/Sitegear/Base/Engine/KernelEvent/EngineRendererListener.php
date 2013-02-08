<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Engine\KernelEvent;

use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

/**
 * Responds to the VIEW kernel event, using the engine's view factory mechanism to generate and render a view.
 */
class EngineRendererListener extends AbstractEngineKernelListener {

	//-- EventSubscriberInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents() {
		return array(
			KernelEvents::VIEW => 'onKernelView',
		);
	}

	//-- Event Listener Methods --------------------

	/**
	 * Perform standard rendering.  This event only fires if the previous (REQUEST) event did not generate a response.
	 *
	 * @param \Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent $event
	 */
	public function onKernelView(GetResponseForControllerResultEvent $event) {
		LoggerRegistry::debug('EngineRendererListener performing render() on VIEW event');
		$request = $event->getRequest();
		$path = explode(':', $request->attributes->get('_route'));
		// Set the module attribute.
		$request->attributes->set('_module', $path[0]);
		// Use either the controller result or the 'natural' name of the view based on the route specifier.
		$request->attributes->set('_view', is_null($event->getControllerResult()) ? $path[1] : $event->getControllerResult());
		// Let the engine render the response and add instrumentation headers; set back to the event.
		$response = $this->getEngine()->renderPage($request);
		$event->setResponse($this->getEngine()->instrumentResponse($response));
	}

}
