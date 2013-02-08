<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

use Sitegear\AbstractSitegearTestCase;

class ArrayUtilitiesTest extends AbstractSitegearTestCase {

	private $associative;
	private $associative2;
	private $mixed;
	private $mixed2;
	private $indexedSequential;
	private $indexedSequential2;
	private $indexedNonSequential;
	private $indexedNonSequential2;

	public function setUp() {
		parent::setUp();
		$this->associative = array(
			'foo' => 'bar',
			'cat' => 'bird',
			'dog' => 'rat'
		);
		$this->associative2 = array(
			'foo' => 'modified',
			'cannibal' => 'human'
		);
		$this->mixed = array(
			'first element',
			'second element',
			'foo' => 'bar',
			42 => 'meaning of life',
			'44' => 'numeric string'
		);
		$this->mixed2 = array(
			'first element of mixed2',
			'foo' => 'modified',
			44 => 'override numeric string with number',
			'last element of mixed2'
		);
		$this->indexedNonSequential = array(
			1 => 'ein',
			7 => 'sieben',
			9 => 'neun',
			11 => 'elf',
			13 => 'dreizen'
		);
		$this->indexedNonSequential2 = array(
			4 => 'vier',
			6 => 'sechs',
			10 => 'zen'
		);
		$this->indexedSequential = array(
			'William I',
			'William II',
			'Henry I',
			'Stephen',
			'Henry II',
			'Richard I',
			'John'
		);
		$this->indexedSequential2 = array(
			'Henry III',
			'Edward I',
			'Edward II',
			'Edward III',
			'Richard II',
			'Henry IV',
			'Henry V',
			'Henry VI'
		);
	}

	public function testIsAssociative() {
		$this->assertTrue(ArrayUtilities::isAssociative($this->associative));
		$this->assertTrue(ArrayUtilities::isAssociative($this->mixed));
		$this->assertFalse(ArrayUtilities::isAssociative($this->indexedSequential));
		$this->assertFalse(ArrayUtilities::isAssociative($this->indexedNonSequential));
	}

	public function testIsIndexed() {
		$this->assertFalse(ArrayUtilities::isIndexed($this->associative));
		$this->assertFalse(ArrayUtilities::isIndexed($this->mixed));
		$this->assertTrue(ArrayUtilities::isIndexed($this->indexedSequential));
		$this->assertTrue(ArrayUtilities::isIndexed($this->indexedNonSequential));
	}

	public function testIsSequential() {
		$this->assertFalse(ArrayUtilities::isSequential($this->associative));
		$this->assertFalse(ArrayUtilities::isSequential($this->mixed));
		$this->assertTrue(ArrayUtilities::isSequential($this->indexedSequential));
		$this->assertFalse(ArrayUtilities::isSequential($this->indexedNonSequential));
	}

	public function testCombineAssociativeAssociative() {
		$expected = array(
			'foo' => 'modified',
			'cat' => 'bird',
			'dog' => 'rat',
			'cannibal' => 'human'
		);
		$this->assertEquals($expected, ArrayUtilities::combine($this->associative, $this->associative2));
	}

	public function testCombineAssociativeMixed() {
		$expected = array(
			'foo' => 'modified',
			'cat' => 'bird',
			'dog' => 'rat',
			0 => 'first element of mixed2',
			44 => 'override numeric string with number',
			45 => 'last element of mixed2'
		);
		$this->assertEquals($expected, ArrayUtilities::combine($this->associative, $this->mixed2));
	}

	public function testCombineAssociativeNonSequential() {
		$expected = array(
			'foo' => 'bar',
			'cat' => 'bird',
			'dog' => 'rat',
			4 => 'vier',
			6 => 'sechs',
			10 => 'zen'
		);
		$this->assertEquals($expected, ArrayUtilities::combine($this->associative, $this->indexedNonSequential2));
	}

	public function testCombineAssociativeSequential() {
		$expected = array(
			'foo' => 'bar',
			'cat' => 'bird',
			'dog' => 'rat',
			0 => 'Henry III',
			1 => 'Edward I',
			2 => 'Edward II',
			3 => 'Edward III',
			4 => 'Richard II',
			5 => 'Henry IV',
			6 => 'Henry V',
			7 => 'Henry VI'
		);
		$this->assertEquals($expected, ArrayUtilities::combine($this->associative, $this->indexedSequential2));
	}

	public function testCombineMixedMixed() {
		$expected = array(
			0 => 'first element of mixed2',
			1 => 'second element',
			42 => 'meaning of life',
			'foo' => 'modified',
			44 => 'override numeric string with number',
			45 => 'last element of mixed2'
		);
		$this->assertEquals($expected, ArrayUtilities::combine($this->mixed, $this->mixed2));
	}

	public function testCombineMixedNonSequential() {
		$expected = array(
			0 => 'first element',
			1 => 'second element',
			4 => 'vier',
			6 => 'sechs',
			10 => 'zen',
			'foo' => 'bar',
			42 => 'meaning of life',
			44 => 'numeric string'
		);
		$this->assertEquals($expected, ArrayUtilities::combine($this->mixed, $this->indexedNonSequential2));
	}

	public function testCombineMixedSequential() {
		$expected = array(
			0 => 'Henry III',
			1 => 'Edward I',
			2 => 'Edward II',
			3 => 'Edward III',
			4 => 'Richard II',
			5 => 'Henry IV',
			6 => 'Henry V',
			7 => 'Henry VI',
			'foo' => 'bar',
			42 => 'meaning of life',
			44 => 'numeric string'
		);
		$this->assertEquals($expected, ArrayUtilities::combine($this->mixed, $this->indexedSequential2));
	}

	public function testCombineNonSequentialNonSequential() {
		$expected = array(
			1 => 'ein',
			4 => 'vier',
			6 => 'sechs',
			7 => 'sieben',
			9 => 'neun',
			10 => 'zen',
			11 => 'elf',
			13 => 'dreizen'
		);
		$this->assertEquals($expected, ArrayUtilities::combine($this->indexedNonSequential, $this->indexedNonSequential2));
	}

	public function testCombineNonSequentialSequential() {
		$expected = array(
			0 => 'Henry III',
			1 => 'Edward I',
			2 => 'Edward II',
			3 => 'Edward III',
			4 => 'Richard II',
			5 => 'Henry IV',
			6 => 'Henry V',
			7 => 'Henry VI',
			9 => 'neun',
			11 => 'elf',
			13 => 'dreizen'
		);
		$this->assertEquals($expected, ArrayUtilities::combine($this->indexedNonSequential, $this->indexedSequential2));
	}

	public function testCombineSequentialSequential() {
		$expected = array(
			'William I',
			'William II',
			'Henry I',
			'Stephen',
			'Henry II',
			'Richard I',
			'John',
			'Henry III',
			'Edward I',
			'Edward II',
			'Edward III',
			'Richard II',
			'Henry IV',
			'Henry V',
			'Henry VI'
		);
		$this->assertEquals($expected, ArrayUtilities::combine($this->indexedSequential, $this->indexedSequential2));
	}

	public function testCombineSequentialNonSequential() {
		$expected = array(
			0 => 'William I',
			1 => 'William II',
			2 => 'Henry I',
			3 => 'Stephen',
			4 => 'vier',
			5 => 'Richard I',
			6 => 'sechs',
			10 => 'zen'
		);
		$this->assertEquals($expected, ArrayUtilities::combine($this->indexedSequential, $this->indexedNonSequential2));
	}

	public function testNested() {
		$array1 = array(
			'foo' => 'bar',
			'sequential-array' => array(
				'first',
				'second',
				'third'
			),
			'associative-array' => array(
				'key1' => 'value1',
				'key2' => 'value2',
				'key3' => array(
					'value3a',
					'value3b',
					'value3c'
				)
			),
			'string-override-with-sequential-array' => 'string',
			'string-override-with-associative-array' => 'string',
			'sequential-array-override-with-string' => array(
				'sequential',
				'array',
				'values'
			),
			'associative-array-override-with-string' => array(
				'foo' => 'bar',
				'baz' => 'xyzzy'
			)
		);
		$array2 = array(
			'foo' => 'modified',
			'new-key' => 'new-value',
			'sequential-array' => array(
				'fourth',
				'fifth'
			),
			'new-sequential-array' => array(
				'new-1',
				'new-2',
				'new-3'
			),
			'associative-array' => array(
				'key1' => 'modified1',
				'key3' => array(
					'value3d-new'
				),
				'key4' => 'new value'
			),
			'string-override-with-sequential-array' => array(
				'this',
				'is',
				'the',
				'array'
			),
			'string-override-with-associative-array' => array(
				'this' => 'is',
				'the' => 'array'
			),
			'sequential-array-override-with-string' => 'override string',
			'associative-array-override-with-string' => 'another override string'
		);
		$expected = array(
			'foo' => 'modified',
			'sequential-array' => array(
				'first',
				'second',
				'third',
				'fourth',
				'fifth'
			),
			'associative-array' => array(
				'key1' => 'modified1',
				'key2' => 'value2',
				'key3' => array(
					'value3a',
					'value3b',
					'value3c',
					'value3d-new'
				),
				'key4' => 'new value'
			),
			'string-override-with-sequential-array' => array(
				'this',
				'is',
				'the',
				'array'
			),
			'string-override-with-associative-array' => array(
				'this' => 'is',
				'the' => 'array'
			),
			'sequential-array-override-with-string' => 'override string',
			'associative-array-override-with-string' => 'another override string',
			'new-key' => 'new-value',
			'new-sequential-array' => array(
				'new-1',
				'new-2',
				'new-3'
			)
		);
		$this->assertEquals($expected, ArrayUtilities::combine($array1, $array2));
	}

	public function testCombineDeepNesting() {
		$array1 = array();
		$array2 = array();
		$nest1 = $array1;
		$nest2 = $array2;
		foreach (range(0, 99) as $i) {
			$nest1['simple-' . $i] = 'array1-' . $i;
			$nest2['simple-' . $i] = 'array2-' . $i;
			$nest1 = $nest1['nested-' . $i] = array();
			$nest2 = $nest2['nested-' . $i] = array();
		}
		$this->assertEquals($nest2, ArrayUtilities::combine($array1, $array2));
	}

}
