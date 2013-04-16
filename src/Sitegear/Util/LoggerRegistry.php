<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

use Psr\Log\LoggerInterface;

/**
 * Global registry for PSR-3 Loggers.  There is a default logger, which is registered by default with a null handler,
 * and is accessible by default through the methods by not passing a $name argument.  Application code should add
 * handlers to this logger by using the register() method, or the get() method and calling pushHandler() on the result.
 *
 * The __call() and __callStatic() magic methods provide shortcuts to the proxy methods:
 *
 * @method static void log($level, $message)
 * @method static void debug($message)
 * @method static void info($message)
 * @method static void notice($message)
 * @method static void warn($message)
 * @method static void error($message)
 * @method static void critical($message)
 * @method static void alert($message)
 * @method static void emergency($message)
 *
 * These methods are all available as both static and instance methods.
 *
 * Registered instances can also be retrieved using the get() method on the singleton instance.
 */
final class LoggerRegistry {

	//-- Attributes --------------------

	/**
	 * @var \Psr\Log\LoggerInterface[]
	 */
	private $loggers = array();

	//-- Singleton --------------------

	/**
	 * @var LoggerRegistry Singleton instance.
	 */
	private static $__instance = null;

	/**
	 * Prevent external instantiation.
	 */
	private function __construct() {}

	/**
	 * Get the singleton instance.
	 *
	 * @return \Sitegear\Util\LoggerRegistry Singleton instance.
	 */
	public static function getInstance() {
		if (is_null(self::$__instance)) {
			self::$__instance = new static();
		}
		return self::$__instance;
	}

	//-- Registry Methods --------------------

	/**
	 * Create and register a new Logger instance with the given name, or if no name is supplied, one will be retrieved
	 * from the logger using its getName() method, if such a method exists.
	 *
	 * @param \Psr\Log\LoggerInterface $logger
	 * @param string|null $name
	 *
	 * @throws \DomainException If no name is given and the given logger does not supply a getName() method.
	 * @throws \InvalidArgumentException If the given name, or the name extracted from the logger, is already registered.
	 */
	public function register($logger, $name=null) {
		if (is_null($name)) {
			if (!method_exists($logger, 'getName')) {
				throw new \DomainException('LoggerRegistry cannot register a logger that does not have a getName() method without providing a name.');
			}
			$name = call_user_func(array( $logger, 'getName' ));
		}
		if ($this->isRegistered($name)) {
			throw new \InvalidArgumentException(sprintf('Cannot register two loggers with the same name, attempted to register "%s" twice', $name));
		}
		$this->loggers[$name] = $logger;
	}

	/**
	 * Remove a previously registered logger.
	 *
	 * @param string $name Name of the logger to remove.
	 *
	 * @throws \InvalidArgumentException If the name is not registered.
	 */
	public function deregister($name) {
		if (!$this->isRegistered($name)) {
			throw new \InvalidArgumentException(sprintf('Cannot deregister Logger with name "%s" because it is not registered', $name));
		}
		unset($this->loggers[$name]);
	}

	/**
	 * Determine if a logger is registered with the given name.  The default logger always exists, even if it has not
	 * been specifically registered.
	 *
	 * @param string $name Name to look for.
	 *
	 * @return boolean Whether or not any logger is registered with the given name.
	 */
	public function isRegistered($name) {
		return array_key_exists($name, $this->loggers);
	}

	/**
	 * Retrieve a previously registered logger.
	 *
	 * @param string $name Name of the logger to retrieve.
	 *
	 * @return \Psr\Log\LoggerInterface Retrieved Logger instance.
	 *
	 * @throws \InvalidArgumentException If the name is not registered.
	 */
	public function get($name) {
		if (!$this->isRegistered($name)) {
			throw new \InvalidArgumentException(sprintf('Cannot retrieve Logger with name "%s"; not registered', $name));
		}
		return $this->loggers[$name];
	}

	//-- Magic Methods --------------------

	/**
	 * @return array Methods that are handled by __call().
	 */
	private static $_proxyMethods = array( 'log', 'debug', 'info', 'notice', 'warn', 'error', 'critical', 'alert', 'emergency' );

	/**
	 * @inheritdoc
	 *
	 * This is a static shorthand call to invoke the proxy methods on the singleton instance.
	 *
	 * In other words, this makes these two calls equivalent:
	 *
	 * LoggerRegistry::info('some message');
	 * LoggerRegistry::getInstance()->info('some message');
	 */
	public static function __callStatic($name, $arguments) {
		if (in_array($name, self::$_proxyMethods)) {
			return call_user_func_array(array( self::getInstance(), $name ), $arguments);
		}
		throw new \BadMethodCallException(sprintf('Unhandled static method call "%s" on LoggerRegistry, only proxy logging methods are supported.', $name));
	}

	/**
	 * @inheritdoc
	 *
	 * Only methods specified in proxyMethods() are allowed.
	 *
	 * $name gives the method to call on the Logger.
	 *
	 * In most cases, $arguments specifies the message and optionally the context values.  This allows calls like:
	 *
	 * LoggerRegistry::getInstance()->warn('message goes here'); // logs "message goes here"
	 * LoggerRegistry::getInstance()->notice('message {foo} goes here', array( 'foo' => 'bar' )); // logs "message bar goes here"
	 *
	 * In the special case of the log() method, $arguments should be the log level, message and optional list of logger
	 * names.  This allows:
	 *
	 * LoggerRegistry::getInstance()->log(Logger::INFO, 'info message'); // logs "info message"
	 * LoggerRegistry::getInstance()->log(Logger::DEBUG, 'debug message {foo}', array( 'foo' => 'bar' )); // logs "info message bar"
	 *
	 * See also __callStatic() for a shorthand version of this.
	 */
	public function __call($name, $arguments) {
		if (in_array($name, self::$_proxyMethods)) {
			foreach ($this->loggers as $logger) {
				call_user_func_array(array( $logger, $name ), $arguments);
			}
		} else {
			throw new \BadMethodCallException(sprintf('Unhandled method call "%s" on LoggerRegistry singleton instance, only proxy logging methods are supported.', $name));
		}
	}

}
