<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

use Sitegear\AbstractSitegearTestCase;

class PhpSourceUtilitiesTest extends AbstractSitegearTestCase {

	public function testSource() {
		$originalFilename = $this->fixtures() . 'site/mock/test-phtml.phtml';
		$original = file_get_contents($originalFilename);
		$formatted = PhpSourceUtilities::formatScript($original);
		$this->assertEquals($original, $formatted);
	}

	public function testParseArguments() {
//		$this->assertSame(array( 1 ), PhpSourceUtilities::parseParameters('1'));
		$this->assertSame(array( 1, 2 ), PhpSourceUtilities::parseArguments('1,2'));
		$this->assertSame(array( 1, 2 ), PhpSourceUtilities::parseArguments('1, 2'));
		$this->assertSame(array( 1, 2 ), PhpSourceUtilities::parseArguments('"1", 2'));
		$this->assertSame(array( 'one', 'two' ), PhpSourceUtilities::parseArguments('one, two'));
		$this->assertSame(array( 'one', 'two' ), PhpSourceUtilities::parseArguments('"one", two'));
		$this->assertSame(array( 'one', 'two' ), PhpSourceUtilities::parseArguments('\'one\', \'two\''));
		$this->assertSame(array( 1, 'two', 'three, and a bit', 'four', 5.6 ), PhpSourceUtilities::parseArguments('1, \'two\', "three, and a bit", four, "5.6"'));
	}

}
