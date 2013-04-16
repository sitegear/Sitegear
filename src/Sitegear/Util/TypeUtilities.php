<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

/**
 * Utilities related to PHP classes and types.
 */
class TypeUtilities {

	/**
	 * Describe the given object for a logging operation.
	 *
	 * @param mixed $object Object to describe.
	 *
	 * @return string Description including the object's type and primitive value if possible.
	 */
	public static function describe($object) {
		if (is_null($object)) {
			$result = 'null';
		} elseif (is_bool($object)) {
			$result = sprintf('boolean(%s)', $object ? 'true' : 'false');
		} elseif (is_string($object) || is_numeric($object)) {
			$result = sprintf('%s(%s)', gettype($object), strval($object));
		} elseif (is_array($object)) {
			$result = sprintf('array[%d]', sizeof($object));
		} elseif (is_object($object) && method_exists($object, '__toString')) {
			$result = sprintf('%s{%s}', get_class($object), strval($object));
		} else {
			$result = sprintf('%s', get_class($object));
		}
		return $result;
	}

	/**
	 * This method does two related things:
	 *
	 * 1. If the $arg argument is a string or an instance of ReflectionClass, check that the class implements the
	 *    interface name or names given in $interfaces, using reflection.  Only if the checks are passed, then an
	 *    instance of the class is created and returned.
	 * 2. If the $arg argument is an existing object, use the instanceof operator to check that the object's class
	 *    implements the interface name or names given in $interfaces.  If the checks are passed, then the object is
	 *    returned unmodified.
	 *
	 * Both forms will throw an exception if the class or object does not implement the required interfaces.
	 *
	 * All class and interface names must be fully namespaced with a leading backslash ("\").
	 *
	 * @param string|\ReflectionClass|object $type Namespaced class name to check, or an instance of ReflectionClass,
	 *   or an object (to perform the type checks only).
	 * @param string $label Label to use in exception message.
	 * @param string|null $baseClass Base class name that must be extended by the specified argument, or null (the
	 *   default) to not check any base class.
	 * @param string|array $interfaces Namespaced interface name that must be implemented by the specified argument, or
	 *   array of interface names.  Use an empty array (the default) to not check any interfaces.
	 * @param array $args Constructor arguments.  Ignored if the $class argument is an already-instantiated object.
	 *
	 * @return object|boolean An object instance, if the class implements all the interfaces, or false if there are any
	 *   interfaces that aren't implemented.
	 *
	 * @throws \DomainException If the class name does not match the given base class name, or does not match one or
	 *   more of the given interface names.
	 * @throws \InvalidArgumentException If the given argument is not a string, ReflectionClass instance or object.
	 */
	public static function buildTypeCheckedObject($type, $label, $baseClass=null, $interfaces=null, array $args=null) {
		// Make sure $interfaces is an array
		if (is_null($interfaces)) {
			$interfaces = array();
		} elseif (!is_array($interfaces)) {
			$interfaces = array( $interfaces );
		}

		// Get the ReflectionClass object for $arg, null if it's already an object
		if (is_string($type)) {
			if (!class_exists($type)) {
				throw new \DomainException(sprintf('TypeUtilities cannot create %s of class "%s", does not exist.', $label, $type));
			}
			$class = new \ReflectionClass($type);
			$className = $type;
		} elseif ($type instanceof \ReflectionClass) {
			$class = $type;
			$className = $type->getName();
		} elseif (is_object($type)) {
			$class = null;
			$className = get_class($type);
		} else {
			throw new \InvalidArgumentException(sprintf('TypeUtilities cannot type check argument that is not a class name or object [%s]', self::describe($type)));
		}

		// Check the interfaces and return the appropriate object
		if (is_null($class)) {
			// It's an object, so use instanceof operator to do type checks
			$object = $type;
			if (!is_null($baseClass) && !$object instanceof $baseClass) {
				throw new \DomainException(sprintf('TypeUtilities cannot confirm %s object of class "%s", does not extend base class "%s".', $label, $className, $baseClass));
			}
			foreach ($interfaces as $interface) {
				if (!$object instanceof $interface) {
					throw new \DomainException(sprintf('TypeUtilities cannot confirm %s object of class "%s", does not implement interface "%s".', $label, $className, $interface));
				}
			}
			// All checks passed, so return the original object unmodified
			return $object;
		} else {
			// It's a ReflectionClass, so use isSubClassOf() and implementsInterface() to do type checks
			if (!is_null($baseClass) && !$class->isSubclassOf($baseClass)) {
				throw new \DomainException(sprintf('TypeUtilities cannot create %s of class "%s", does not extend base class "%s".', $label, $className, $baseClass));
			}
			foreach ($interfaces as $interface) {
				if (!$class->implementsInterface($interface)) {
					throw new \DomainException(sprintf('TypeUtilities cannot create %s of class "%s", does not implement interface "%s".', $label, $className, $interface));
				}
			}
			// All checks passed, so return a new instance
			return $class->newInstanceArgs($args ?: array());
		}
	}

	/**
	 * Determine the correct class name for the given argument.  This is effectively the reverse operation to the
	 * typeCheckedObject() method:
	 *
	 * 1. If $arg is a string, it is checked to ensure it represents an existing class, and returned.
	 * 2. If $arg is a ReflectionClass instance, the result of getName() is returned.
	 * 3. If $arg is an object, its class name is returned using get_class().
	 *
	 * If $arg is not one of the above, or is a string that does not represent a valid class name, then an exception is
	 * thrown.
	 *
	 * @param string|\ReflectionClass|object $arg Argument to get the class name for.
	 *
	 * @return string Class name including namespace.
	 *
	 * @throws \InvalidArgumentException If $arg is not a string or object.
	 * @throws \DomainException If $arg is a string that is not a valid class name.
	 */
	public static function getClassName($arg) {
		$result = null;
		if (is_string($arg)) {
			if (!class_exists($arg)) {
				throw new \DomainException(sprintf('Cannot confirm class name specified as string: "%s"', $arg));
			}
			$result = $arg;
		} elseif ($arg instanceof \ReflectionClass) {
			$result = $arg->getName();
		} elseif (is_object($arg)) {
			$result = get_class($arg);
		} else {
			throw new \InvalidArgumentException(sprintf('Cannot get the class name of non-object that is not a string [%s]', self::describe($arg)));
		}
		return $result;
	}

	/**
	 * Get the parameters list for the given callable.
	 *
	 * Code borrowed from \Symfony\Component\HttpKernel\ControllerResolver.
	 *
	 * @param callable|\ReflectionMethod|\Closure|object|string $callable
	 *
	 * @return \ReflectionParameter[]
	 */
	public static function getParameters($callable) {
		if (is_object($callable) && !$callable instanceof \Closure) {
			if ($callable instanceof \ReflectionFunctionAbstract) {
				$r = $callable;
			} else {
				$obj = new \ReflectionObject($callable);
				$r = $obj->getMethod('__invoke');
			}
		} elseif (is_array($callable)) {
			$r = new \ReflectionMethod($callable[0], $callable[1]);
		} else {
			$r = new \ReflectionFunction($callable);
		}
		return $r->getParameters();
	}

	/**
	 * Determine the argument list for the given callable method, depending on the given values.  This allows for a
	 * combination of fixed values, type hinting detection, and declared defaults.
	 *
	 * The fixed values are used first.  These are required arguments that are assumed to exist in any implementation,
	 * in the order given.  This is effectively the starting point for the resulting argument list.
	 *
	 * When all the fixed values are exhausted, the remaining values are filled by first checking the type against each
	 * of the given typed values.  Any matching typed value ias appended to the resulting argument list.  Typed values
	 * are used once only.  If there are multiple arguments of the same type, then two values should be supplied in the
	 * typed values argument list.
	 *
	 * If there is no matching typed value, then the remaining values will be used.  When the remaining values are
	 * exhausted, then the parameter's default value will be used.
	 *
	 * If any parameter has no matching typed arguments, and there are no fixed values or remaining values left to
	 * process, and does not have a default value, then a runtime exception is issued.
	 *
	 * @param callable|\ReflectionMethod|\Closure|object|string $callable Method or function reference, name or
	 *   reflection.
	 * @param array|null $fixedValues Values that are required at the start of the argument list.  These will be used
	 *   before any other values in determining the argument list.  If there are more elements than the number of
	 *   arguments specified by the callable, then not all elements will be used.
	 * @param array|null $typedValues Values that are detected by type hinting.  Not all these values are necessarily
	 *   used, only those that are matched by type against the callable's argument list.
	 * @param array|null $remainingValues Values that are used when all the fixed values are used, and there are no
	 *   matching typed values.  Remaining values are used in preference to the parameter default values.
	 *
	 * @return array Array of arguments to pass to the method call, e.g. using call_user_fuc_array().
	 *
	 * @throws \RuntimeException If there are insufficient matching values provided to pass for all required arguments.
	 */
	public static function getArguments($callable, array $fixedValues=null, array $typedValues=null, array $remainingValues=null) {
		$arguments = array();
		foreach ($parameters = self::getParameters($callable) as $parameterIndex => $parameter) {
			if (sizeof($fixedValues) > 0) {
				$arguments[] = array_shift($fixedValues);
			} else {
				$matchedParameter = false;
				foreach ($typedValues ?: array() as $typedValueIndex => $typedValue) {
					if (($matchedParameter === false) && $parameter->getClass() && $parameter->getClass()->isInstance($typedValue)) {
						$matchedParameter = $typedValueIndex;
					}
				}
				if ($matchedParameter !== false) {
					$match = array_splice($typedValues, $matchedParameter, 1);
					$arguments[] = $match[0];
				} else {
					if (!empty($remainingValues)) {
						$arguments[] = array_shift($remainingValues);
					} elseif ($parameter->isDefaultValueAvailable()) {
						$arguments[] = $parameter->getDefaultValue();
					} else {
						throw new \RuntimeException(sprintf('TypeUtilities cannot determine arguments for callable [%s] based on supplied values: %d unhandled parameters and %d arguments processed', TypeUtilities::describe($callable), (sizeof($parameters) - $parameterIndex), sizeof($arguments)));
					}
				}
			}
		}
		return $arguments;
	}

	/**
	 * Call the given callable, using arguments determined from the given values using the getArguments() method.
	 *
	 * @param callable $callable Method or function reference, name or reflection object.
	 * @param array|null $fixedValues Values that are required at the start of the argument list.  These will be used
	 *   before any other values in determining the argument list.  If there are more elements than the number of
	 *   arguments specified by the callable, then not all elements will be used.
	 * @param array|null $typedValues Values that are detected by type hinting.  Not all these values are necessarily
	 *   used, only those that are matched by type against the callable's argument list.
	 * @param array|null $remainingValues Values that are used when all the fixed values are used, and there are no
	 *   matching typed values.  Remaining values are used in preference to the parameter default values.
	 *
	 * @return mixed Result of invoking the given callable using the arguments as determined using supplied values.
	 *
	 * @throws \RuntimeException If there are insufficient matching values provided to pass for all required arguments.
	 */
	public static function invokeCallable($callable, array $fixedValues=null, array $typedValues=null, array $remainingValues=null) {
		return call_user_func_array($callable, TypeUtilities::getArguments($callable, $fixedValues, $typedValues, $remainingValues));
	}

	/**
	 * Taking an array of format strings, and an array of values to replace into the format strings (using
	 * TokenUtilities::replaceTokens()), find the first format string which corresponds to an existing class and return
	 * a ReflectionClass object representing that class.  If none of the formats correspond to an existing class,
	 * then null is returned.
	 *
	 * @param string[] $namespaces
	 * @param string $className
	 *
	 * @return null|\ReflectionClass
	 */
	public static function firstExistingClass(array $namespaces, $className) {
		$class = null;
		foreach ($namespaces as $namespace) {
			if (is_null($class) && class_exists($namespaceClassName = sprintf('%s\\%s', $namespace, $className))) {
				$class = new \ReflectionClass($namespaceClassName);
			}
		}
		return $class;
	}

}
