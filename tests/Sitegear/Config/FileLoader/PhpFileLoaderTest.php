<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Config\FileLoader;

use Sitegear\AbstractSitegearTestCase;

class PhpFileLoaderTest extends AbstractSitegearTestCase {

	/**
	 * @var \Sitegear\Config\FileLoader\PhpFileLoader
	 */
	private $loader;

	public function setUp() {
		parent::setUp();
		$this->loader = new PhpFileLoader();
	}

	public function testSupports() {
		// Only support existing PHP files...
		$this->assertTrue($this->loader->supports($this->fixtures() . 'test-no-env-specific.php'));
		// Don't support anything else...
		$this->assertFalse($this->loader->supports($this->fixtures() . 'this-file-does-not-exist.php'));
		$this->assertFalse($this->loader->supports($this->fixtures() . 'test-no-env-specific.json'));
		$this->assertFalse($this->loader->supports($this->fixtures() . 'dummy.xml'));
	}

	public function testLoad() {
		$this->assertInternalType('array', $this->loader->load($this->fixtures() . 'test-no-env-specific.php'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testLoadFileNotExist() {
		$this->loader->load($this->fixtures() . 'this-file-does-not-exist.php');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testLoadFileInvalidType() {
		$this->loader->load($this->fixtures() . 'this-file-does-not-exist.json');
	}

}
