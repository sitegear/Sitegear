<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View;

use Sitegear\Util\HtmlUtilities;
use Sitegear\Util\TypeUtilities;
use Sitegear\Util\NameUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;

/**
 * The View class provides a dynamic implementation of ViewInterface.
 *
 * The MagicContext class provides a very simple DSL (domain specific language) which can be used within view scripts.
 * The DSL is also very similar to the method syntax used within module methods.  Each element that is rendered has its
 * own View, which is self-generated based on the usage of the DSL in view scripts.
 *
 * The View implementation of ArrayAccess is a proxy to a DataSeekingArrayObject, with the parent view as the
 * parent to the array object.
 *
 * '''This class makes heavy use of magic methods, see the documentation for __call() and __toString() for details.'''
 *
 * The following magic methods are defined for known core modules:
 *
 * @method \Sitegear\View\View content()
 * @method \Sitegear\View\View doctrine()
 * @method \Sitegear\View\View file()
 * @method \Sitegear\View\View navigation()
 * @method \Sitegear\View\View pageMessages()
 * @method \Sitegear\View\View resourcesIntegration()
 * @method \Sitegear\View\View userIntegration()
 * @method \Sitegear\View\View version()
 *
 * The following magic methods are defined for known extension modules:
 *
 * @method \Sitegear\View\View customer()
 * @method \Sitegear\View\View forms()
 * @method \Sitegear\View\View google()
 * @method \Sitegear\View\View locations()
 * @method \Sitegear\View\View mailChimp()
 * @method \Sitegear\View\View news()
 * @method \Sitegear\View\View products()
 * @method \Sitegear\View\View swiftMailer()
 *
 * The following magic methods match the SPECIAL_TARGET_* constants:
 *
 * @method \Sitegear\View\View template()
 * @method \Sitegear\View\View section()
 * @method \Sitegear\View\View component()
 * @method \Sitegear\View\View resources()
 * @method \Sitegear\View\View strings()
 * @method \Sitegear\View\View item()
 *
 * The following magic methods are defined for common section names:
 *
 * @method \Sitegear\View\View main()
 * @method \Sitegear\View\View script()
 * @method \Sitegear\View\View article()
 * @method \Sitegear\View\View sidebar()
 * @method \Sitegear\View\View aside()
 * @method \Sitegear\View\View header()
 * @method \Sitegear\View\View footer()
 *
 * The following magic methods are provided for common template names:
 *
 * @method \Sitegear\View\View default()
 * @method \Sitegear\View\View ajax()
 * @method \Sitegear\View\View mobile()
 * @method \Sitegear\View\View minimal()
 *
 * No magic methods are defined here for component names, so currently these will register a "method undefined" warning
 * in IDEs.
 *
 * The following magic methods are provided for common string placeholder names:
 *
 * @method \Sitegear\View\View title()
 * @method \Sitegear\View\View keywords()
 *
 * The following provides a more specific return value:
 *
 * @method \Sitegear\Engine\SitegearEngine getEngine()
 */
class View extends AbstractView {

	//-- Target Level Constants --------------------

	/**
	 * The first target is the module name (or special target at module level).
	 */
	const TARGET_LEVEL_MODULE = 0;

	/**
	 * The second target is the method name (or special target at method level).
	 */
	const TARGET_LEVEL_METHOD = 1;

	//-- Attributes --------------------

	/**
	 * @var boolean Whether rendering is currently in progress.
	 */
	private $rendering = false;

	//-- ViewInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function render() {
		LoggerRegistry::debug('View::render()');
		$content = null;
		$this->rendering = true;
		if ($this->getTargetCount() === 2) {
			$request = $this->getRequest();

			// The arguments to the module definition are decorators, set them now
			call_user_func_array(array( $this, 'activateDecorators'), $this->getTargetArguments(self::TARGET_LEVEL_MODULE));

			// Create a relevant context
			$context = $this->getEngine()->getViewFactory()->buildViewContext($this, $this->getRequest());

			// Check for and execute a target controller
			$targetController = $context->getTargetController($this, $request);
			$targetControllerResult = null;
			if (!is_null($targetController) && is_callable($targetController)) {
				$targetControllerResult = TypeUtilities::invokeCallable(
					$targetController,
					null,
					array( $this, $request ),
					$this->getTargetArguments(self::TARGET_LEVEL_METHOD) ?: array()
				);
			}

			// A result of false means don't render anything.
			if ($targetControllerResult !== false) {
				// Use the context to render the result
				$content = $context->render($this->getEngine()->getViewFactory()->getRendererRegistry(), $targetControllerResult) ?: '';

				// Decorate using active decorators
				foreach ($this->getActiveDecorators() as $active) {
					$decorator = $this->getEngine()->getViewFactory()->getDecoratorRegistry()->getDecorator($active['name']);
					$content = TypeUtilities::invokeCallable(
						array( $decorator, 'decorate' ),
						array( $content ),
						array( $this, $request ),
						$active['arguments']
					);
				}
			}
		} else {
			// Incorrect number of targets; exactly 2 expected
			$targets = $this->getTargetCount() === 1 ? 'target' : 'targets';
			throw new \LogicException(sprintf('Error in view script; exactly 2 targets expected ("$view->module()->method()"); %d %s encountered', $this->getTargetCount(), $targets));
		}

		// Ensure we are returning a string value
		$this->rendering = false;
		return $content ?: '';
	}

	//-- Shortcut Methods --------------------

	/**
	 * Shortcut to retrieve the resources manager from the view factory.  This is useful in view scripts.
	 *
	 * @return \Sitegear\View\ResourcesManager\ResourcesManagerInterface
	 */
	public function getResourcesManager() {
		return $this->getEngine()->getViewFactory()->getResourcesManager();
	}

	/**
	 * Shortcut to retrieve the strings manager from the view factory.  This is useful in view scripts.
	 *
	 * @return \Sitegear\View\StringsManager\StringsManagerInterface
	 */
	public function getStringsManager() {
		return $this->getEngine()->getViewFactory()->getStringsManager();
	}

	//-- Magic Methods --------------------

	/**
	 * @inheritdoc
	 *
	 * This implementation allows the use of a simple syntax in view scripts to render nested view scripts.
	 *
	 * $view->section()->[sectionName]()
	 * $view->component()->[componentName]()
	 * $view->[moduleName]()->[componentName]()
	 *
	 * These all follow the same pattern; the first call specifies the source of the content, and the second call
	 * specifies the exact target.  The special strings 'section', 'component' and 'template' can be used in the first
	 * position.  The 'section' special string maps to the controller module; the 'component' and 'template' special
	 * strings map to the Content module.
	 *
	 * For every occurrence encountered of the above syntax patterns in a view script, a new View object is
	 * created dynamically.  If the syntax is incorrect, e.g. if there are more or less than 2 chained method calls,
	 * then an error message is output.
	 */
	public function __call($name, $arguments) {
		if (!$this->rendering) {
			// We are not rendering yet, so push a target onto the stack.
			$this->pushTarget(NameUtilities::convertToDashedLower($name), $arguments);
		} else {
			// We are already rendering this view, and the view script is requesting a child view; create it and call
			// the requested method on it (which will set the new view's first target, because the child view is not
			// rendering).
			$childView = $this->getEngine()->getViewFactory()->buildView($this->getRequest(), $this);
			return call_user_func_array(array( $childView, $name ), $arguments);
		}
		return $this;
	}

	/**
	 * See the documentation for __call() for details.
	 *
	 * @return string Rendered content.
	 */
	public function __toString() {
		try {
			// Delegate to the render() method.
			return $this->render();
		} catch (\Exception $exception) {
			// Throwing any Exception from __toString() is a fatal error (bravo, php, bra-vo) so we have no other
			// choice but to catch any exception here and do something with it.  Delete any buffered content and
			// display a plain error page.
			// TODO Make this a proper error-500 page but prevent feedback loops
			while (ob_get_level() > 1) {
				ob_end_clean();
			}
			return HtmlUtilities::exception(
				$exception,
				$this->getEngine()->getSiteInfo()->getAdministratorName(),
				$this->getEngine()->getSiteInfo()->getAdministratorEmail()
			);
		}
	}

}
