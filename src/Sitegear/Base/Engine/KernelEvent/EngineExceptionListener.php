<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Engine\KernelEvent;

use Sitegear\Util\HtmlUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Responds to the EXCEPTION kernel event.
 */
class EngineExceptionListener extends AbstractEngineKernelListener {

	//-- EventSubscriberInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents() {
		return array(
			KernelEvents::EXCEPTION => 'onKernelException'
		);
	}

	//-- Event Listener Methods --------------------

	/**
	 * Handle exceptions that occur during the request handling process.
	 *
	 * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
	 *
	 * @throws \Exception|\Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException|\Symfony\Component\HttpKernel\Exception\HttpException
	 *   As a fallback behaviour only, if a sensible error page cannot be generated.
	 */
	public function onKernelException(GetResponseForExceptionEvent $event) {
		LoggerRegistry::debug('EngineExceptionListener performing handleException() on EXCEPTION event');
		$exception = $event->getException();
		try {
			// Determine the status code to use for the error page (and which error page is displayed).
			$statusCode = ($exception instanceof HttpException) ? $exception->getStatusCode() :
					(($exception instanceof FileNotFoundException) ? 404 : 500);

			// Set route and template attributes to error page defaults.
			$event->getRequest()->attributes->add(array(
				'_status' => $statusCode,
				'_route' => $this->getEngine()->getErrorRoute($statusCode),
				'_route_params' => array( 'exception' => $exception, 'statusCode' => $statusCode ),
				'_template' => $this->getEngine()->getErrorTemplate()
			));

			// Store the exception and status code in the top-level view data.
			$page = $this->getEngine()->getViewFactory()->getPage();
			$page['exception'] = $exception;
			$page['status-code'] = $statusCode;

			// Perform standard rendering using a proxy event.
			$renderer = new EngineRendererListener($this->getEngine());
			$renderEvent = new GetResponseForControllerResultEvent($event->getKernel(), $event->getRequest(), HttpKernelInterface::SUB_REQUEST, sprintf('error-%3d', $statusCode));
			$renderer->onKernelView($renderEvent);

			// Copy the response from the proxy event back to the event being handled.
			$event->setResponse($renderEvent->getResponse());
		} catch (\Exception $e) {
			try {
				// If there is an error above, try to display the fallback error page, i.e. black-and-white error message
				$event->setResponse(Response::create(HtmlUtilities::exception($event->getException())));
			} catch (\Exception $e2) {
				// If another error occurs, the best thing to do is throw the original error.
				throw $exception;
			}
		}
	}

}
