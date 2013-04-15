<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Config\FileLoader;

use Sitegear\AbstractSitegearTestCase;

class JsonFileLoaderTest extends AbstractSitegearTestCase {

	/**
	 * @var \Sitegear\Config\FileLoader\JsonFileLoader
	 */
	private $loader;

	public function setUp() {
		parent::setUp();
		$this->loader = new JsonFileLoader();
	}

	public function testSupports() {
		// Only support existing JSON files...
		$this->assertTrue($this->loader->supports($this->fixtures() . 'test-no-env-specific.json'));
		// Don't support anything else...
		$this->assertFalse($this->loader->supports($this->fixtures() . 'this-file-does-not-exist.json'));
		$this->assertFalse($this->loader->supports($this->fixtures() . 'test-no-env-specific.php'));
		$this->assertFalse($this->loader->supports($this->fixtures() . 'dummy.xml'));
	}

	public function testLoad() {
		$this->assertInternalType('array', $this->loader->load($this->fixtures() . 'test-no-env-specific.json'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testLoadFileNotExist() {
		$this->loader->load($this->fixtures() . 'this-file-does-not-exist.json');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testLoadFileInvalidType() {
		$this->loader->load($this->fixtures() . 'this-file-does-not-exist.php');
	}

}
