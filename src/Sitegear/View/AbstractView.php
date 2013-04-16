<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View;

use Sitegear\Engine\EngineInterface;
use Sitegear\Util\ArrayUtilities;

use Sitegear\Util\LoggerRegistry;
use Sitegear\Util\TypeUtilities;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a partial implementation of the ViewInterface, which:
 *
 * * Stores the view factory, request and parent view in simple attributes, exposing them with getters.
 * * Stores an array of targets, and implements the target inspection and management methods of the interface.
 * * Stores the view data using any object implementing ArrayAccess, which is passed to the constructor (thus also
 *   allowing pre-population of data).
 * * Stores the currently active decorators in an array.
 * * Implements a simple exception handling mechanism, and exposes the exception via the exception() method.
 *
 * This abstract implementation requires only the render() method to be complete.
 */
abstract class AbstractView implements ViewInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Engine\EngineInterface
	 */
	private $engine;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	private $request;

	/**
	 * @var \Sitegear\View\ViewInterface
	 */
	private $parent;

	/**
	 * @var array[] Stack of targets, each being an associative array of 'target' and 'arguments' keys.
	 */
	private $targets = array();

	/**
	 * @var \ArrayAccess
	 */
	private $data;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Engine\EngineInterface $engine
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Sitegear\View\ViewInterface|null $parent
	 * @param \ArrayAccess|null $data Data object, or null to use an empty \ArrayObject.
	 */
	public function __construct(EngineInterface $engine, Request $request, ViewInterface $parent=null, $data=null) {
		LoggerRegistry::debug('new AbstractView()');
		$this->engine = $engine;
		$this->request = $request;
		$this->parent = $parent;
		$this->data = $data ?: new \ArrayObject();
	}

	//-- ViewInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getEngine() {
		return $this->engine;
	}

	/**
	 * @inheritdoc
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * @inheritdoc
	 */
	public function pushTarget($target, array $arguments=null) {
		LoggerRegistry::debug('AbstractView::pushTarget({target}, {arguments})', array( 'target' => TypeUtilities::describe($target), 'arguments' => TypeUtilities::describe($arguments) ));
		array_push($this->targets, array(
			'target' => $target,
			'arguments' => $arguments ?: array()
		));
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function pushTargets(array $targets) {
		foreach ($targets as $entry) {
			if (is_array($entry)) {
				$target = $entry['target'];
				$arguments = $entry['arguments'];
			} else {
				$target = $entry;
				$arguments = array();
			}
			$this->pushTarget($target, $arguments);
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function clearTargets() {
		LoggerRegistry::debug('AbstractView::clearTargets()');
		$this->targets = array();
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getTargetCount() {
		return sizeof($this->targets);
	}

	/**
	 * @inheritdoc
	 */
	public function getTarget($index=null) {
		if (is_null($index)) {
			$index = sizeof($this->targets) - 1;
		} elseif ($index >= sizeof($this->targets)) {
			throw new \OutOfBoundsException(sprintf('View does not contain a target at index %d', $index));
		}
		return $this->targets[$index]['target'];
	}

	/**
	 * @inheritdoc
	 */
	public function getTargetArguments($index=null) {
		if (is_null($index)) {
			$index = sizeof($this->targets) - 1;
		} elseif ($index >= sizeof($this->targets)) {
			throw new \OutOfBoundsException(sprintf('View does not contain a target at index %d', $index));
		}
		return $this->targets[$index]['arguments'] ?: array();
	}

	//-- ArrayAccess Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function offsetExists($offset) {
		return $this->data->offsetExists($offset);
	}

	/**
	 * @inheritdoc
	 */
	public function offsetGet($offset) {
		return $this->data->offsetGet($offset);
	}

	/**
	 * @inheritdoc
	 */
	public function offsetSet($offset, $value) {
		$this->data->offsetSet($offset, $value);
	}

	/**
	 * @inheritdoc
	 */
	public function offsetUnset($offset) {
		$this->data->offsetUnset($offset);
	}

	//-- Internal Methods --------------------

	protected function getRequest() {
		return $this->request;
	}

}
