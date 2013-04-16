<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Config;

use Sitegear\AbstractSitegearTestCase;
use Sitegear\Config\ConfigContainer;
use Sitegear\Config\ConfigLoader;
use Sitegear\Info\SitegearEnvironmentInfoProvider;

class ConfigContainerTest extends AbstractSitegearTestCase {

	/**
	 * @var ConfigContainer
	 */
	private $config;

	public function setUp() {
		parent::setUp();
		$this->config = new ConfigContainer(new ConfigLoader(new SitegearEnvironmentInfoProvider('testing')));
	}

	public function testEmptyConfig() {
		$this->assertNull($this->config->get('any.key'));
		$this->assertNull($this->config->get('anotherKey'));
		$this->assertNull($this->config->get('missing.key'));
	}

	public function testJsonConfigNoEnvironmentSpecificFile() {
		$this->config->merge($this->fixtures() . 'test-no-env-specific.json');
		$this->assertEquals('value', $this->config->get('any.key'));
		$this->assertInternalType('array', $this->config->get('anotherKey'));
		$this->assertSameSize(array(1, 2), $this->config->get('anotherKey'));
		$this->assertNull($this->config->get('missing.key'));
		$this->assertInternalType('string', $this->config->get('string-array-conversion'));
	}

	public function testJsonConfigWithEnvironmentSpecificFile() {
		$this->config->merge($this->fixtures() . 'test-with-env-specific.json');
		$this->assertEquals('overridden', $this->config->get('any.key'));
		$this->assertInternalType('array', $this->config->get('anotherKey'));
		$this->assertSameSize(array(1, 2, 3), $this->config->get('anotherKey'));
		$this->assertNull($this->config->get('missing.key'));
		$this->assertInternalType('array', $this->config->get('string-array-conversion'));
	}

	public function testPhpConfigNoEnvironmentSpecificFile() {
		$this->config->merge($this->fixtures() . 'test-no-env-specific.php');
		$this->assertEquals('value', $this->config->get('any.key'));
		$this->assertInternalType('array', $this->config->get('anotherKey'));
		$this->assertSameSize(array(1, 2), $this->config->get('anotherKey'));
		$this->assertNull($this->config->get('missing.key'));
		$this->assertInternalType('string', $this->config->get('string-array-conversion'));
	}

	public function testPhpConfigWithEnvironmentSpecificFile() {
		$this->config->merge($this->fixtures() . 'test-with-env-specific.php');
		$this->assertEquals('overridden', $this->config->get('any.key'));
		$this->assertInternalType('array', $this->config->get('anotherKey'));
		$this->assertSameSize(array(1, 2, 3), $this->config->get('anotherKey'));
		$this->assertNull($this->config->get('missing.key'));
		$this->assertInternalType('array', $this->config->get('string-array-conversion'));
	}

	public function testMergeMultipleFiles() {
		$this->config->merge($this->fixtures() . 'test-with-env-specific.json');
		$this->config->merge($this->fixtures() . 'test-with-env-specific.php');
		$this->assertStringStartsWith('PHP', $this->config->get('name'));
	}

	public function testMergeMultipleFilesPreferExisting() {
		$this->config->merge($this->fixtures() . 'test-with-env-specific.json');
		$this->config->merge($this->fixtures() . 'test-with-env-specific.php', true);
		$this->assertStringStartsWith('JSON', $this->config->get('name'));
	}

	public function testMergeInlineArray() {
		$this->config->merge($this->fixtures() . 'test-with-env-specific.json');
		$this->config->merge($this->fixtures() . 'test-with-env-specific.php');
		$this->config->merge(array(
			'name' => 'This is the data from the array'
		));
		$this->assertStringEndsWith('from the array', $this->config->get('name'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testMergeObject() {
		$this->config->merge(new \StdClass());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testMergePrimitiveType() {
		$this->config->merge(42);
	}

}
