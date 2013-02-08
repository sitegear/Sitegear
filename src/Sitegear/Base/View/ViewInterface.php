<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\View;

/**
 * The ViewInterface defines the behaviour of objects responsible for storing the state of a view, and for determining
 * the context for rendering views, and delegating rendering to the rendering context.  The ArrayAccess interface
 * allows views to transport data between controller methods and view scripts.
 */
interface ViewInterface extends \ArrayAccess {

	/**
	 * Engine that is using this view.
	 *
	 * @return \Sitegear\Base\Engine\EngineInterface
	 */
	public function getEngine();

	/**
	 * Retrieve the parent view.
	 *
	 * @return null|ViewInterface Parent view, or null if this is the top level view.
	 */
	public function getParent();

	/**
	 * Add a target to the top of the stack.
	 *
	 * @param string|array $target Target to add.
	 * @param null|array $arguments Array of arguments.
	 *
	 * @return self
	 */
	public function pushTarget($target, array $arguments=null);

	/**
	 * Add a number of targets to the top of the stack in order.
	 *
	 * Each entry in the given array is either a string, which is the name of the target with no arguments, or an array
	 * containing 'target' and 'arguments' keys.
	 *
	 * @param array $targets Array of targets to add.
	 *
	 * @return self
	 */
	public function pushTargets(array $targets);

	/**
	 * Remove all targets and reset to the default state.
	 *
	 * @return self
	 */
	public function clearTargets();

	/**
	 * Get the number of targets set in this view.
	 *
	 * @return int
	 */
	public function getTargetCount();

	/**
	 * Retrieve the target specifier at the given index.
	 *
	 * @param int|null $index The index to retrieve, or null to retrieve the last target.
	 *
	 * @return string
	 *
	 * @throws \OutOfBoundsException If the given index is out of bounds.
	 */
	public function getTarget($index=null);

	/**
	 * Retrieve the arguments for the target at the given index.
	 *
	 * @param int|null $index
	 *
	 * @return array
	 *
	 * @throws \OutOfBoundsException If the given index is out of bounds.
	 */
	public function getTargetArguments($index=null);

	/**
	 * Enable one or more decorators.
	 *
	 * @varargs Decorators to enable.  Any indexed arrays should be expanded recursively.  Each string may simply
	 *   specify the decorator name, or may use the form accepted by PhpSourceUtilities::parseFunctionCall().
	 *   Associative arrays must have a 'decorator' key, and may have an optional 'arguments' key which maps to either
	 *   a single primitive (single parameter) or an indexed array (multiple parameters).
	 *
	 * @return self
	 *
	 * @throws \InvalidArgumentException If any specification is invalid.
	 */
	public function applyDecorators();

	/**
	 * Does the main part of rendering the view.
	 *
	 * @return string Rendered view content.
	 *
	 * @throws \LogicException If the view is not ready for rendering.
	 */
	public function render();

}
