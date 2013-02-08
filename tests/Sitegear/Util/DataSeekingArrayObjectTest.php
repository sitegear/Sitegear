<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

use Sitegear\AbstractSitegearTestCase;

class DataSeekingArrayObjectTest extends AbstractSitegearTestCase {

	/**
	 * @var DataSeekingArrayObject
	 */
	private $object1;

	/**
	 * @var DataSeekingArrayObject
	 */
	private $object2;

	/**
	 * @var DataSeekingArrayObject
	 */
	private $object3;

	public function setUp() {
		$this->object1 = new DataSeekingArrayObject();
		$this->object1['key1'] = 'value1';

		$this->object2 = new DataSeekingArrayObject($this->object1);
		$this->object2['key2'] = 'value2';

		$this->object3 = new DataSeekingArrayObject($this->object2);
		$this->object3['key3'] = 'value3';
	}

	public function testNoParent() {
		$this->assertArrayHasKey('key1', $this->object1);
		$this->assertEquals('value1', $this->object1['key1']);
		$this->assertArrayNotHasKey('key2', $this->object1);
		$this->assertArrayNotHasKey('key3', $this->object1);
	}

	public function testParentChild() {
		$this->assertTrue($this->object2->offsetExists('key1'));
		// The above line replaces the following line of code (which is commented out)
		// See http://stackoverflow.com/questions/1538124/php-array-key-exists-and-spl-arrayaccess-interface-not-compatible
		// for the reason for this and other lines commented out below
//		$this->assertArrayHasKey('key1', $this->object2);
		$this->assertEquals('value1', $this->object2['key1']);
		$this->assertArrayHasKey('key2', $this->object2);
		$this->assertEquals('value2', $this->object2['key2']);
		$this->assertArrayNotHasKey('key3', $this->object2);
	}

	public function testAncestorDescendant() {
		$this->assertTrue($this->object3->offsetExists('key1'));
//		$this->assertArrayHasKey('key1', $this->object3);
		$this->assertEquals('value1', $this->object3['key1']);
		$this->assertTrue($this->object3->offsetExists('key2'));
//		$this->assertArrayHasKey('key2', $this->object3);
		$this->assertEquals('value2', $this->object3['key2']);
		$this->assertArrayHasKey('key3', $this->object3);
		$this->assertEquals('value3', $this->object3['key3']);
	}

}
