<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\StringsManager;

use Sitegear\AbstractSitegearTestCase;

class StringsManagerTest extends AbstractSitegearTestCase {

	/**
	 * @var StringsManager
	 */
	private $manager;

	public function setUp() {
		$this->manager = new StringsManager();
	}

	public function testRenderEmpty() {
		$this->assertEmpty($this->manager->render('no-strings'));
	}

	public function testAppend() {
		$this->manager->append('test', 'first');
		$this->assertEquals('first', $this->manager->render('test'));

		$this->manager->append('test', 'second');
		$this->assertEquals('first, second', $this->manager->render('test'));

		$this->manager->append('test', 'third', 'fourth', 'fifth');
		$this->assertEquals('first, second, third, fourth, fifth', $this->manager->render('test'));
	}

	public function testPrepend() {
		$this->manager->prepend('test', 'first');
		$this->assertEquals('first', $this->manager->render('test'));

		$this->manager->prepend('test', 'second');
		$this->assertEquals('second, first', $this->manager->render('test'));

		$this->manager->prepend('test', 'third', 'fourth', 'fifth');
		$this->assertEquals('fifth, fourth, third, second, first', $this->manager->render('test'));
	}

	public function testAppendPrepend() {
		$this->manager->append('test', 'first');
		$this->manager->prepend('test', 'second');
		$this->manager->append('test', 'third');
		$this->manager->prepend('test', 'fourth');
		$this->assertEquals('fourth, second, first, third', $this->manager->render('test'));
	}

	public function testCustomSeparator() {
		$this->manager->append('test', 'first');
		$this->manager->append('test', 'second');
		$this->manager->append('test', 'third', 'fourth', 'fifth');
		$this->manager->setSeparator('test', ' :: ');
		$this->assertEquals(' :: ', $this->manager->getSeparator('test'));
		$this->assertEquals('first :: second :: third :: fourth :: fifth', $this->manager->render('test'));
	}

	public function testKeys() {
		$this->manager->append('one', 'one');
		$this->manager->append('two', 'two');
		$this->manager->append('three', 'three');
		$this->assertEquals(array( 'one', 'two', 'three' ), $this->manager->getKeys());
	}

}
