<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

use Sitegear\AbstractSitegearTestCase;

class ExtensionMimeTypeGuesserTest extends AbstractSitegearTestCase {

	/**
	 * @var ExtensionMimeTypeGuesser
	 */
	private $guesser;

	public function setUp() {
		parent::setUp();
		$this->guesser = new ExtensionMimeTypeGuesser($this->fixtures() . 'mime.types.test');
	}

	public function testGuessSingleExtension() {
		$this->assertEquals('text/css', $this->guesser->guess($this->fixtures() . 'stylesheet.css'));
	}

	public function testGuessMultipleExtension() {
		$this->assertEquals('application/xml', $this->guesser->guess($this->fixtures() . 'dummy.xml'));
		$this->assertEquals('application/xml', $this->guesser->guess($this->fixtures() . 'dummy.xsl'));
		$this->assertEquals('application/xml', $this->guesser->guess($this->fixtures() . 'dummy.xsd'));
	}

	public function testGuessCommentedOutNotFound() {
		$this->assertNull($this->guesser->guess($this->fixtures() . 'image.png'));
	}

	public function testGuessNotPresentNotFound() {
		$this->assertNull($this->guesser->guess($this->fixtures() . 'another.jpg'));
	}

	/**
	 * @expectedException \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
	 */
	public function testGuessFileNotFound() {
		$this->guesser->guess('/this/file/does/not/exist.txt');
	}

//	To run this test, create the mentioned file, chown it to another user and chmod it to 700
//	/**
//	 * @expectedException \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException
//   */
//	public function testGuessFileNotReadable() {
//		$this->guesser->guess(dirname(dirname(__DIR__)) . '/fixtures/unreadable.txt');
//	}

	/**
	 * @expectedException \LogicException
	 */
	public function testGuessNoDataFile() {
		$invalidGuesser = new ExtensionMimeTypeGuesser('/no/data');
		$invalidGuesser->guess($this->fixtures() . 'stylesheet.css');
	}

}
