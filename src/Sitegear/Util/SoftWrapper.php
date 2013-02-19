<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

/**
 * SoftWrapper allows any object, or non-object, to be "wrapped" by allowing delegate calls to the wrapped object only
 * if they exist.
 *
 * For example, consider a resource Foo that is only available in certain circumstances.  There might be some code that
 * looks like this:
 *
 *     $this->foo = ($fooEnabled && class_exists('Foo')) ? new Foo() : null;
 *
 * Now suppose that Foo resource is accessible by a method named `getFoo()`, then you will have code like this in other
 * classes:
 *
 *     $container->getFoo()->doSomething();
 *
 * In the case where `Foo` is not available, with the above code it will be null, which will cause an exception.
 * Something like this is usually required:
 *
 *     if (!is_null($container->getFoo())) {
 *         $container->getFoo()->doSomething();
 *     }
 *
 * Using `SoftWrapper`, we can overcome this very simply:
 *
 *     $this->foo = new SoftWrapper(($fooEnabled && class_exists('Foo')) ? new Foo() : null);
 *
 * The call to `doSomething()` will no longer cause an error because `SoftWrapper` simply returns null for all method
 * calls.
 */
class SoftWrapper {

	//-- Attributes --------------------

	/**
	 * @var object
	 */
	private $object;

	//-- Constructor --------------------

	/**
	 * @param mixed $object
	 */
	public function __construct($object) {
		$this->object = is_object($object) ? $object : new \StdClass();
	}

	//-- Magic Methods --------------------

	/**
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed|null
	 */
	public function __call($name, $arguments) {
		if (method_exists($this->object, $name)) {
			return call_user_func_array(array( $this->object, $name ), $arguments);
		}
		return null;
	}

}
