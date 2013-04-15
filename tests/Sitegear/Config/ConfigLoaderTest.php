<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Config;

use Sitegear\AbstractSitegearTestCase;

class ConfigLoaderTest extends AbstractSitegearTestCase {

	/**
	 * @var ConfigLoader
	 */
	private $loader;

	public function setUp() {
		parent::setUp();
		$this->loader = new ConfigLoader('testing');
	}

	public function testFileLoaderRegistration() {
		$dummyLoaderClassName = '\\Sitegear\\Mock\\MockFileLoader';
		$this->assertFalse($this->loader->hasFileLoader($dummyLoaderClassName));
		$this->loader->registerFileLoader($dummyLoaderClassName);
		$this->assertTrue($this->loader->hasFileLoader($dummyLoaderClassName));
		$this->loader->deregisterFileLoader($dummyLoaderClassName);
		$this->assertFalse($this->loader->hasFileLoader($dummyLoaderClassName));
	}

	/**
	 * @expectedException \DomainException
	 */
	public function testRegisterClassNotExists() {
		$this->loader->registerFileLoader('\\This\\Class\\Does\\Not\\Exist');
	}

	/**
	 * @expectedException \DomainException
	 */
	public function testRegisterInvalidFileLoader() {
		$this->loader->registerFileLoader('\\StdClass');
	}

	public function testLoadJsonConfigNoEnvironmentSpecificFile() {
		$data = $this->loader->load($this->fixtures() . 'test-no-env-specific.json');
		$this->assertEquals('value', $data['any']['key']);
		$this->assertInternalType('array', $data['anotherKey']);
		$this->assertSameSize(array(1, 2), $data['anotherKey']);
		$this->assertInternalType('string', $data['string-array-conversion']);
	}

	public function testLoadJsonConfigWithEnvironmentSpecificFile() {
		$data = $this->loader->load($this->fixtures() . 'test-with-env-specific.json');
		$this->assertEquals('overridden', $data['any']['key']);
		$this->assertInternalType('array', $data['anotherKey']);
		$this->assertSameSize(array(1, 2, 3), $data['anotherKey']);
		$this->assertInternalType('array', $data['string-array-conversion']);
	}

	public function testLoadPhpConfigNoEnvironmentSpecificFile() {
		$data = $this->loader->load($this->fixtures() . 'test-no-env-specific.php');
		$this->assertEquals('value', $data['any']['key']);
		$this->assertInternalType('array', $data['anotherKey']);
		$this->assertSameSize(array(1, 2), $data['anotherKey']);
		$this->assertInternalType('string', $data['string-array-conversion']);
	}

	public function testLoadPhpConfigWithEnvironmentSpecificFile() {
		$data = $this->loader->load($this->fixtures() . 'test-with-env-specific.php');
		$this->assertEquals('overridden', $data['any']['key']);
		$this->assertInternalType('array', $data['anotherKey']);
		$this->assertSameSize(array(1, 2, 3), $data['anotherKey']);
		$this->assertInternalType('array', $data['string-array-conversion']);
	}

	private static $testData = array(
		"name" => "PHP test file",
		"description" => "This is a PHP configuration file for testing the Sitegear Config component.",
		"any" => array(
			"key" => "value"
		),
		"anotherKey" => array( "a value", "another value" ),
		"string-array-conversion" => "initial string value"
	);


	public function testLoadArray() {
		$this->assertEquals(self::$testData, $this->loader->load(self::$testData));
	}

	public function testLoadArrayObject() {
		$this->assertEquals(self::$testData, $this->loader->load(new \ArrayObject(self::$testData)));
	}

	public function testLoadConfigContainerObject() {
		$config = new \Sitegear\Config\Container\SimpleConfigContainer($this->loader);
		$config->merge(self::$testData);
		$this->assertEquals(self::$testData, $this->loader->load($config));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testLoadObject() {
		$this->loader->load(new \StdClass());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testLoadPrimitiveType() {
		$this->loader->load(42);
	}

	public function testNoDefaultLoadersConfig() {
		$loaderNoDefaults = new ConfigLoader('testing', false);
		$this->assertEmpty($loaderNoDefaults->load($this->fixtures() . 'test-no-env-specific.json'));
		$this->assertEmpty($loaderNoDefaults->load($this->fixtures() . 'test-with-env-specific.json'));
	}

}
