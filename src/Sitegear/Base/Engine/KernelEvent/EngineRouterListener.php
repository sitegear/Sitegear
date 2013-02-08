<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Engine\KernelEvent;

use Sitegear\Util\LoggerRegistry;
use Sitegear\Util\TypeUtilities;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

/**
 * Responds to the REQUEST kernel event, in a manner modelled on the Symfony components implementation:
 * \Symfony\Component\HttpKernel\EventListener\RouterListener.
 */
class EngineRouterListener extends AbstractEngineKernelListener {

	//-- AbstractEngineKernelListener Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents() {
		return array(
			KernelEvents::REQUEST => array( 'onKernelRequest', 1024 )
		);
	}

	//-- Event Listener Methods --------------------

	/**
	 * Setup the route parameters.
	 *
	 * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	public function onKernelRequest(GetResponseEvent $event) {
		LoggerRegistry::debug('EngineRouterListener performing start() on REQUEST event');
		$this->setRoutingAttributes($event->getRequest());
		$this->setTemplateAttributes($event->getRequest());
		LoggerRegistry::debug('EngineRouterListener will proceed with standard rendering');
	}

	//-- Internal Methods --------------------

	/**
	 * Set the route attributes on the request, "_route" and "_route_params"
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	private function setRoutingAttributes(Request $request) {
		try {
			// Setup the request context.
			$context = new RequestContext();
			$context->fromRequest($request);
			// Perform URL matching.
			$matcher = new UrlMatcher($this->getEngine()->getRouteMap(), $context);
			$parameters = $matcher->match($request->getPathInfo());
			// Set the routing parameters on the request object.
			$request->attributes->add($parameters);
			unset($parameters['_route']);
			unset($parameters['_controller']);
			$request->attributes->set('_route_params', $parameters);
		} catch (ResourceNotFoundException $e) {
			// No route was found, this is a HTTP 404 error.
			throw new NotFoundHttpException('The page you requested is not available.', $e);
		} catch (MethodNotAllowedException $e) {
			// An invalid HTTP method was used, this is a HTTP 405 error.
			$message = sprintf('EngineRouterListener could not find a controller route match for "%s %s": Method Not Allowed (Allow: %s)', $request->getMethod(), $request->getPathInfo(), strtoupper(implode(', ', $e->getAllowedMethods())));
			throw new MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
		}
	}

	/**
	 * Set the template for the given request as a request attribute "_template".
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return null|string
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	private function setTemplateAttributes($request) {
		$template = null;
		// Check each entry in the template map for a match against the given URL.
		foreach ($this->getEngine()->getTemplateMap() as $entry) {
			if (preg_match($entry['compiled-pattern'], $request->getPathInfo())) {
				$template = $entry['template'];
			}
		}
		// If no match was found, then we have a problem.
		if (is_null($template)) {
			throw new NotFoundHttpException(sprintf('EngineRouterListener could not find a template match for URL "%s"', $request->getPathInfo()));
		}
		// Set the template parameters on the request object.
		$request->attributes->set('_template', $template);
	}

}
