<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\View;

use Sitegear\Base\View\AbstractView;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Base\Engine\EngineInterface;
use Sitegear\Core\View\Context\ComponentViewContext;
use Sitegear\Core\View\Context\SectionViewContext;
use Sitegear\Core\View\Context\TemplateViewContext;
use Sitegear\Core\View\Context\ResourcesViewContext;
use Sitegear\Core\View\Context\StringsViewContext;
use Sitegear\Core\View\Context\ModuleItemViewContext;
use Sitegear\Util\DataSeekingArrayObject;
use Sitegear\Util\PhpSourceUtilities;
use Sitegear\Util\ArrayUtilities;
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
 * @method \Sitegear\Core\View\View content()
 * @method \Sitegear\Core\View\View doctrine()
 * @method \Sitegear\Core\View\View file()
 * @method \Sitegear\Core\View\View navigation()
 * @method \Sitegear\Core\View\View resourcesIntegration()
 * @method \Sitegear\Core\View\View userIntegration()
 * @method \Sitegear\Core\View\View version()
 *
 * The following magic methods are defined for known extension modules:
 *
 * @method \Sitegear\Core\View\View customer()
 * @method \Sitegear\Core\View\View forms()
 * @method \Sitegear\Core\View\View google()
 * @method \Sitegear\Core\View\View locations()
 * @method \Sitegear\Core\View\View mailChimp()
 * @method \Sitegear\Core\View\View news()
 * @method \Sitegear\Core\View\View products()
 * @method \Sitegear\Core\View\View swiftMailer()
 *
 * The following magic methods match the SPECIAL_TARGET_* constants:
 *
 * @method \Sitegear\Core\View\View template()
 * @method \Sitegear\Core\View\View section()
 * @method \Sitegear\Core\View\View component()
 * @method \Sitegear\Core\View\View resources()
 * @method \Sitegear\Core\View\View strings()
 * @method \Sitegear\Core\View\View item()
 *
 * The following magic methods are defined for common section names:
 *
 * @method \Sitegear\Core\View\View main()
 * @method \Sitegear\Core\View\View script()
 * @method \Sitegear\Core\View\View article()
 * @method \Sitegear\Core\View\View sidebar()
 * @method \Sitegear\Core\View\View aside()
 * @method \Sitegear\Core\View\View header()
 * @method \Sitegear\Core\View\View footer()
 *
 * The following magic methods are provided for common template names:
 *
 * @method \Sitegear\Core\View\View default()
 * @method \Sitegear\Core\View\View ajax()
 * @method \Sitegear\Core\View\View mobile()
 * @method \Sitegear\Core\View\View minimal()
 *
 * No magic methods are defined here for component names, so currently these will register a "method undefined" warning
 * in IDEs.
 *
 * The following magic methods are provided for common string placeholder names:
 *
 * @method \Sitegear\Core\View\View title()
 * @method \Sitegear\Core\View\View keywords()
 *
 * The following provides a more specific return value:
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
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

	//-- Special Target Constants --------------------

	/**
	 * Special target name at the module level for rendering templates from the default module.
	 */
	const SPECIAL_TARGET_MODULE_TEMPLATE = 'template';

	/**
	 * Special target name at the module level for rendering sections from the controller module for the current URL.
	 */
	const SPECIAL_TARGET_MODULE_SECTION = 'section';

	/**
	 * Special target name at the module level for rendering components from the default module.
	 */
	const SPECIAL_TARGET_MODULE_COMPONENT = 'component';

	/**
	 * Special target name at the module level for rendering resources.
	 */
	const SPECIAL_TARGET_MODULE_RESOURCES = 'resources';

	/**
	 * Special target name at the module level for rendering strings.
	 */
	const SPECIAL_TARGET_MODULE_STRINGS = 'strings';

	/**
	 * Special target name at the method level to represent that a module-specific item should be rendered.
	 */
	const SPECIAL_TARGET_METHOD_ITEM = 'item';

	//-- Attributes --------------------

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	private $request;

	/**
	 * @var array[] Array of associative arrays, each of which has a 'name' key and an 'arguments' key.
	 */
	private $activeDecorators;

	/**
	 * @var boolean Whether rendering is currently in progress.
	 */
	private $rendering;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\Engine\EngineInterface $engine
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Sitegear\Base\View\ViewInterface|null $parent
	 */
	public function __construct(EngineInterface $engine, Request $request, ViewInterface $parent=null) {
		LoggerRegistry::debug('Constructing View');
		parent::__construct($engine, $parent, new DataSeekingArrayObject($parent));
		$this->activeDecorators = array();
		$this->rendering = false;
		$this->request = $request;
	}

	//-- ViewInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function render() {
		LoggerRegistry::debug('View rendering content');
		$content = null;
		$this->rendering = true;
		if ($this->getTargetCount() === 2) {
			// The arguments to the module definition are decorators, set them now
			call_user_func_array(array( $this, 'applyDecorators'), $this->getTargetArguments(self::TARGET_LEVEL_MODULE));

			// Create a relevant context
			$context = $this->createContext();

			// Check for and execute a target controller
			$targetController = $context->getTargetController($this, $this->request);
			$targetControllerResult = null;
			if (!is_null($targetController) && is_callable($targetController)) {
				$targetControllerResult = TypeUtilities::invokeCallable(
					$targetController,
					null,
					array( $this, $this->request ),
					$this->getTargetArguments(self::TARGET_LEVEL_METHOD) ?: array()
				);
			}

			// A result of false means don't render anything.
			if ($targetControllerResult !== false) {
				// Use the context to render the result
				$content = $context->render($this->getEngine()->getViewFactory()->getRendererRegistry(), $this, $this->request, $targetControllerResult) ?: '';

				// Decorate using active decorators
				foreach ($this->activeDecorators as $active) {
					$decorator = $this->getEngine()->getViewFactory()->getDecoratorRegistry()->getDecorator($active['name']);
					$content = TypeUtilities::invokeCallable(
						array( $decorator, 'decorate' ),
						array( $content ),
						array( $this, $this->request ),
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

	/**
	 * {@inheritDoc}
	 */
	public function applyDecorators() {
		foreach (func_get_args() as $arg) {
			if (is_array($arg) && ArrayUtilities::isIndexed($arg)) {
				// An indexed array, recurse with each element of the array as a separate argument.
				call_user_func_array(array( $this, 'applyDecorators'), $arg);
			} elseif (is_array($arg)) {
				// An associative array, ensure it has an 'arguments' key.
				$this->activeDecorators[] = array_merge(array( 'arguments' => array() ), $arg);
			} elseif (is_string($arg)) {
				// A string, use the parseFunctionCall() utility method.
				$this->activeDecorators[] = PhpSourceUtilities::parseFunctionCall($arg);
			} else {
				// Unhandled type.
				throw new \InvalidArgumentException(sprintf('Cannot use [%s] as decorator specification', TypeUtilities::describe($arg)));
			}
		}
		return $this;
	}

	//-- Shortcut Methods --------------------

	/**
	 * Shortcut to retrieve the resources manager from the view factory.  This is useful in view scripts.
	 *
	 * @return \Sitegear\Base\View\Resources\ResourcesManagerInterface
	 */
	public function getResourcesManager() {
		return $this->getEngine()->getViewFactory()->getResourcesManager();
	}

	/**
	 * Shortcut to retrieve the strings manager from the view factory.  This is useful in view scripts.
	 *
	 * @return \Sitegear\Base\View\Strings\StringsManagerInterface
	 */
	public function getStringsManager() {
		return $this->getEngine()->getViewFactory()->getStringsManager();
	}

	//-- Internal Methods --------------------

	/**
	 * Create a relevant context for this view.
	 *
	 * @return \Sitegear\Base\View\Context\ViewContextInterface
	 */
	protected function createContext() {
		LoggerRegistry::debug(sprintf('View creating context for "%s"', $this->getTarget(self::TARGET_LEVEL_MODULE)));
		// Check for special targets at the module level
		switch ($this->getTarget(self::TARGET_LEVEL_MODULE)) {
			// $view->template()->{templateName}()
			case self::SPECIAL_TARGET_MODULE_TEMPLATE:
				$context = new TemplateViewContext($this);
				break;

			// $view->section()->{sectionName}()
			case self::SPECIAL_TARGET_MODULE_SECTION:
				$index = $this->getEngine()->getViewFactory()->getIndexSectionName();
				$section = $this->getEngine()->getViewFactory()->getFallbackSectionName();
				$context = new SectionViewContext($this, $index, $section);
				break;

			// $view->component()->{componentName}()
			case self::SPECIAL_TARGET_MODULE_COMPONENT:
				$context = new ComponentViewContext($this);
				break;

			// $view->resources()->{resourceTypeName}()
			case self::SPECIAL_TARGET_MODULE_RESOURCES:
				$context = new ResourcesViewContext($this);
				break;

			// $view->strings()->{stringName}()
			case self::SPECIAL_TARGET_MODULE_STRINGS:
				$context = new StringsViewContext($this);
				break;

			// $view->{moduleName}()...
			default:
				// No special target at the module level, check at the method level
				switch ($this->getTarget(self::TARGET_LEVEL_METHOD)) {
					// $view->{moduleName}()->item()
					case self::SPECIAL_TARGET_METHOD_ITEM:
						$context = new ModuleItemViewContext($this);
						break;

					// $view->{moduleName}()->{componentName}()
					default:
						$context = new ComponentViewContext($this);
				}
		}
		return $context;
	}

	//-- Magic Methods --------------------

	/**
	 * {@inheritDoc}
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
			$childView = $this->getEngine()->getViewFactory()->buildView($this->request, $this);
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
			// choice but to catch any exception here and do something with it.  In development environments, it is
			// probably useful to see the error message, but in other environments we should hide it.
			if ($this->getEngine()->getEnvironmentInfo()->isDevMode()) {
				return HtmlUtilities::exception(
					$exception,
					$this->getEngine()->getSiteInfo()->getAdministratorName(),
					$this->getEngine()->getSiteInfo()->getAdministratorEmail()
				);
			} else {
				return '';
			}
		}
	}

}
