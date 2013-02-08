<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

use Sitegear\AbstractSitegearTestCase;

use Monolog\Logger;

class LoggerRegistryTest extends AbstractSitegearTestCase {

	const LOGGER_NAME = '__test__';

	const LOGGER_NAME_NEVER_REGISTERED = '__never_registered__';

	public function testGetInstance() {
		LoggerRegistry::getInstance();
	}

	public function testRegister() {
		$this->assertFalse(LoggerRegistry::getInstance()->isRegistered(self::LOGGER_NAME));
		LoggerRegistry::getInstance()->register(new Logger(self::LOGGER_NAME));
		$this->assertTrue(LoggerRegistry::getInstance()->isRegistered(self::LOGGER_NAME));
		LoggerRegistry::getInstance()->deregister(self::LOGGER_NAME);
		$this->assertFalse(LoggerRegistry::getInstance()->isRegistered(self::LOGGER_NAME));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testRegisterDuplicateName() {
		LoggerRegistry::getInstance()->register(new Logger(self::LOGGER_NAME), self::LOGGER_NAME);
		LoggerRegistry::getInstance()->register(new Logger(self::LOGGER_NAME), self::LOGGER_NAME);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testDeregisterNotRegistered() {
		LoggerRegistry::getInstance()->deregister(self::LOGGER_NAME_NEVER_REGISTERED);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetNotRegistered() {
		LoggerRegistry::getInstance()->get(self::LOGGER_NAME_NEVER_REGISTERED);
	}

	public function test__callStatic() {
		LoggerRegistry::log(Logger::DEBUG, 'Test message using log()');
		LoggerRegistry::debug('Test message using debug()');
	}

	/**
	 * @expectedException \BadMethodCallException
	 */
	public function test__callStaticInvalidArguments() {
		LoggerRegistry::thisMethodDoesNotExist('foo', 'bar');
	}

	/**
	 * @expectedException \BadMethodCallException
	 */
	public function test__callStaticNoArguments() {
		/** @noinspection PhpParamsInspection */
		LoggerRegistry::debug();
	}

}
