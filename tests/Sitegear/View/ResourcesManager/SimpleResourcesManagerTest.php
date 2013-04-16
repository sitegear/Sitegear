<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\ResourcesManager;

use Sitegear\AbstractSitegearTestCase;

class SimpleResourcesManagerTest extends AbstractSitegearTestCase {

	/**
	 * @var SimpleResourcesManager
	 */
	private $manager;

	public function setUp() {
		$this->manager = new SimpleResourcesManager();
	}

	public function testRegisterType() {
		$this->assertFalse($this->manager->isTypeRegistered('test-type'));
		$this->manager->registerType('test-type', '<!-- This is a test type %url% -->');
		$this->assertTrue($this->manager->isTypeRegistered('test-type'));
		$this->assertEquals('<!-- This is a test type %url% -->', $this->manager->getFormat('test-type'));
		$this->assertEquals(array( 'test-type' ), $this->manager->types());
	}

	public function testRegisterTypeMap() {
		$this->assertFalse($this->manager->isTypeRegistered('test-type'));
		$this->assertFalse($this->manager->isTypeRegistered('test-type-2'));
		$this->manager->registerTypeMap(array(
			'test-type' => '<!-- This is a test type %url% -->',
			'test-type-2' => '<!-- %url% -->'
		));
		$this->assertTrue($this->manager->isTypeRegistered('test-type'));
		$this->assertTrue($this->manager->isTypeRegistered('test-type-2'));
		$this->assertEquals('<!-- This is a test type %url% -->', $this->manager->getFormat('test-type'));
		$this->assertEquals('<!-- %url% -->', $this->manager->getFormat('test-type-2'));
		$this->assertEquals(array( 'test-type', 'test-type-2' ), $this->manager->types());
	}

	public function testGetFormatUnregisteredType() {
		$this->assertNull($this->manager->getFormat('unregistered-type'));
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testRegisterTypeTwice() {
		$this->manager->registerType('test-error', '<!-- %url% -->');
		$this->manager->registerType('test-error', '<!-- %url% -->');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testRegisterTypeInvalidKey() {
		$this->manager->registerType('some@error', '');
	}

	public function testRegister() {
		$this->manager->registerType('test-type', '<!-- This is a test type %url% -->');
		$this->manager->register('foo:bar', 'test-type', 'some/url', true);
		$this->assertTrue($this->manager->isRegistered('foo:bar'));
		$this->assertEquals('test-type', $this->manager->getType('foo:bar'));
		$this->assertEquals('some/url', $this->manager->getUrl('foo:bar'));
		$this->assertEquals('<!-- This is a test type some/url -->' . PHP_EOL, $this->manager->render('test-type'));
	}

	public function testRegisterThenActivate() {
		$this->manager->registerType('test-type', '<!-- This is a test type %url% -->');
		$this->manager->register('foo:bar', 'test-type', 'some/url');
		$this->assertTrue($this->manager->isRegistered('foo:bar'));
		$this->assertEquals('test-type', $this->manager->getType('foo:bar'));
		$this->assertEquals('some/url', $this->manager->getUrl('foo:bar'));
		$this->assertEquals('', $this->manager->render('test-type'));
		$this->manager->activate('foo:bar');
		$this->assertEquals('<!-- This is a test type some/url -->' . PHP_EOL, $this->manager->render('test-type'));
	}

	public function testActivateDependencies() {
		$this->manager->registerType('test-type', '<!-- This is a test type %url% -->');
		$this->manager->register('foo:bar', 'test-type', 'some/url');
		$this->manager->register('foo:xyzzy', 'test-type', 'some/url/xyzzy', false, array( 'foo:bar' ));
		$this->assertFalse($this->manager->isActive('foo:bar'));
		$this->assertFalse($this->manager->isActive('foo:xyzzy'));
		$this->manager->activate('foo:xyzzy');
		$this->assertTrue($this->manager->isActive('foo:bar'));
		$this->assertTrue($this->manager->isActive('foo:xyzzy'));
	}

	public function testRenderShortcut() {
		$this->manager->registerType('test-type', '<!-- This is a test type %url% -->');
		$this->manager->register('foo:bar', 'test-type', 'some/url');
		$this->manager->register('foo:xyzzy', 'test-type', 'some/url/xyzzy', true, array( 'foo:bar' ));
		$this->assertEquals($this->manager->render('test-type'), $this->manager->testType());
	}

	/**
	 * @expectedException \DomainException
	 */
	public function testRegisterUnknownType() {
		$this->manager->register('some:key', 'unknown-type', '/some/url');
	}

	/**
	 * @expectedException \DomainException
	 */
	public function testUrlsUnknownType() {
		$this->manager->getAllUrls('unknown-type');
	}

	/**
	 * @expectedException \DomainException
	 */
	public function testRenderUnknownType() {
		$this->manager->render('unknown-type');
	}

}
