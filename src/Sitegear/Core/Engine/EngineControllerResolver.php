<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Engine;

use Sitegear\Util\NameUtilities;
use Sitegear\Util\TypeUtilities;

use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Implementation of ControllerResolverInterface that is coupled with the Engine.
 */
class EngineControllerResolver implements ControllerResolverInterface {

	//-- Constants --------------------

	const FORMAT_CONTROLLER_METHOD_NAME = '%sController';

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Core\Engine\Engine
	 */
	private $engine;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Core\Engine\Engine $engine
	 */
	public function __construct(Engine $engine) {
		$this->engine = $engine;
	}

	//-- ControllerResolverInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getController(Request $request) {
		$route = explode(':', $request->attributes->get('_route'));
		$module = $this->engine->getModule($route[0]);
		$method = sprintf(self::FORMAT_CONTROLLER_METHOD_NAME, NameUtilities::convertToCamelCase($route[1]));
		if (!method_exists($module, $method)) {
			$module = $this->engine->getModule($this->engine->config('engine.module-resolution.default-controller'));
			$method = sprintf(self::FORMAT_CONTROLLER_METHOD_NAME, $this->engine->config('engine.module-resolution.default-controller-method'));
		}
		$request->attributes->set('_controller_module', $module);
		return array( $module, $method );
	}

	/**
	 * {@inheritDoc}
	 */
	public function getArguments(Request $request, $controller) {
		return TypeUtilities::getArguments(
			$controller,
			null,
			array( $this->engine->getViewFactory()->getPage(), $request ),
			$request->attributes->get('_route_params') ?: array()
		);
	}

}
