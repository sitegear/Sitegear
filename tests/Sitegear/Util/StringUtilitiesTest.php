<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

use Sitegear\AbstractSitegearTestCase;

class StringUtilitiesTest extends AbstractSitegearTestCase {

	public function testFindTokens() {
		$this->assertEquals(array(), StringUtilities::findTokens(''));
		$this->assertEquals(array(), StringUtilities::findTokens('This string has no tokens'));

		// Single percent symbols, percent symbols separated by whitespace, etc
		$this->assertEquals(array(), StringUtilities::findTokens('This string has only 50% of a token (i.e. no tokens)'));
		$this->assertEquals(array(), StringUtilities::findTokens('This string has 200% of 50% of a token (i.e. no tokens)'));

		$this->assertEquals(array( 'foo' ), StringUtilities::findTokens('This %foo% has 1 token'));
		$this->assertEquals(array( 'foo', 'bar' ), StringUtilities::findTokens('This %foo% has 2 %bar%'));
		$this->assertEquals(array( 'foo', 'bar', 'baz' ), StringUtilities::findTokens('This %foo% has 3 %bar% and is %baz%'));

		// Repeated tokens
		$this->assertEquals(array( 'foo' ), StringUtilities::findTokens('This %foo% has 1 token.  It is a well tested %foo%.'));
		$this->assertEquals(array( 'foo', 'bar' ), StringUtilities::findTokens('This %foo% has 2 %bar%.  This %foo% has 2 %bar%.'));
		$this->assertEquals(array( 'foo', 'bar' ), StringUtilities::findTokens('This %foo% has 2 %bar%.  2 %bar% has this %foo%.'));
	}

	public function testReplaceTokens() {
		// Base case
		$this->assertEquals('', StringUtilities::replaceTokens(
			'',
			array()
		));

		// Base case with ignored tokens
		$this->assertEquals('', StringUtilities::replaceTokens(
			'',
			array( 'foo' => 'bar', 'baz' => 'xyzzy' )
		));

		// No change for no tokens.
		$this->assertEquals('This %foo% has 3 %bar% and is %baz%', StringUtilities::replaceTokens(
			'This %foo% has 3 %bar% and is %baz%',
			array()
		));

		// Normal case
		$this->assertEquals('This sentence has 3 tokens and is well tested', StringUtilities::replaceTokens(
			'This %foo% has 3 %bar% and is %baz%',
			array(
				'foo' => 'sentence',
				'bar' => 'tokens',
				'baz' => 'well tested'
			)
		));
		$this->assertEquals('This car has 3 turbos and is stupidly fast', StringUtilities::replaceTokens(
			'This %foo% has 3 %bar% and is %baz%',
			array(
				'foo' => 'car',
				'bar' => 'turbos',
				'baz' => 'stupidly fast'
			)
		));

		// Repeated tokens.
		$this->assertEquals('This sentence has 2 tokens.  This sentence has 2 tokens.', StringUtilities::replaceTokens(
			'This %foo% has 2 %bar%.  This %foo% has 2 %bar%.',
			array(
				'foo' => 'sentence',
				'bar' => 'tokens'
			)
		));
		$this->assertEquals('This sentence has 2 tokens.  2 tokens has this sentence.', StringUtilities::replaceTokens(
			'This %foo% has 2 %bar%.  2 %bar% has this %foo%.',
			array(
				'foo' => 'sentence',
				'bar' => 'tokens'
			)
		));

		// Ignored tokens.
		$this->assertEquals('This %foo% has 3 tokens and is well tested', StringUtilities::replaceTokens(
			'This %foo% has 3 %bar% and is %baz%',
			array(
				'foo' => array( 'ignored' => 'sentence' ),
				'bar' => 'tokens',
				'baz' => 'well tested'
			)
		));
	}

}
