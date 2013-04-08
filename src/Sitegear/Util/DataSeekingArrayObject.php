<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

/**
 * ArrayObject extension which dynamically seeks in parent instances, which may be any ArrayAccess implementation.
 * When used in a chain, DataSeekingArrayObject provides a convenient method of access to data from parent contexts.
 *
 * Note that this method overrides only the getter methods of ArrayObject, not the setter methods.  That means, if an
 * offset is retrieved, it will seek parent contexts, but if an offset is deleted or its value modified, it affects
 * this object only.
 */
class DataSeekingArrayObject extends \ArrayObject {

	//-- Attributes --------------------

	/**
	 * @var \ArrayAccess|null
	 */
	private $parent;

	//-- Constructor --------------------

	/**
	 * @param \ArrayAccess|null $parent Parent object, used to seek upwards for data.
	 * @param array|object|null $input Passed to base \ArrayObject class constructor.
	 * @param int $flags Passed to base \ArrayObject class constructor.
	 * @param string $iteratorClass Passed to base \ArrayObject class constructor.
	 */
	public function __construct($parent=null, $input=null, $flags=0, $iteratorClass='ArrayIterator') {
		parent::__construct($input ?: array(), $flags, $iteratorClass);
		$this->parent = $parent;
	}

	//-- ArrayAccess Methods --------------------

	/**
	 * @inheritdoc
	 *
	 * This implementation detects the specified offset in this context or any ancestor contexts.
	 */
	public function offsetExists($offset) {
		return parent::offsetExists($offset) ||
				(!is_null($this->parent) && $this->parent->offsetExists($offset));
	}

	/**
	 * @inheritdoc
	 *
	 * This implementation retrieves the specified offset in this context or any ancestor contexts.
	 */
	public function offsetGet($offset) {
		return parent::offsetExists($offset) ? parent::offsetGet($offset) :
				(!is_null($this->parent) ? $this->parent->offsetGet($offset) : null);
	}

}
