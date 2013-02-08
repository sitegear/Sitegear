<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

use Sitegear\AbstractSitegearTestCase;

// TODO FIXME
//class SectionPathUtilitiesTest extends AbstractSitegearTestCase {
//
//	const INDEX_NAME = 'index';
//	const FALLBACK_NAME = 'fallback';
//
//	public function testGetViewPathOptionsZeroElementPath() {
//		$options = SectionPathUtilities::getSectionPathOptions('', self::INDEX_NAME, self::FALLBACK_NAME);
//		$this->assertEquals(2, sizeof($options));
//		$this->assertStringEndsWith('index', $options[0]);
//		$this->assertStringEndsWith('fallback', $options[1]);
//		$this->assertEquals($options, SectionPathUtilities::getSectionPathOptions('/', self::INDEX_NAME, self::FALLBACK_NAME));
//	}

//	public function testGetViewPathOptionsZeroElementPathNoFallback() {
//		$options = SectionPathUtilities::getSectionPathOptions('', self::INDEX_NAME, false);
//		$this->assertEquals(1, sizeof($options));
//		$this->assertStringEndsWith('index', $options[0]);
//		$this->assertEquals($options, SectionPathUtilities::getSectionPathOptions('/', self::INDEX_NAME, false));
//	}
//
//	public function testGetViewPathOptionsSingleElementPath() {
//		$options = SectionPathUtilities::getSectionPathOptions('foo', self::INDEX_NAME, self::FALLBACK_NAME);
//		$this->assertEquals(4, sizeof($options));
//		$this->assertStringEndsWith('index', $options[0]);
//		$this->assertStringEndsWith('foo', $options[1]);
//		for ($i=2; $i<sizeof($options); $i++) {
//			$this->assertStringEndsWith('fallback', $options[$i], 'getViewPathOptions() with single element path returns correct value in position ' . $i);
//		}
//		$this->assertEquals($options, SectionPathUtilities::getSectionPathOptions('/foo', self::INDEX_NAME, self::FALLBACK_NAME));
//		$this->assertEquals($options, SectionPathUtilities::getSectionPathOptions('/foo/', self::INDEX_NAME, self::FALLBACK_NAME));
//	}
//
//	public function testGetViewPathOptionsSingleElementPathNoFallback() {
//		$options = SectionPathUtilities::getSectionPathOptions('foo', self::INDEX_NAME, false);
//		$this->assertEquals(2, sizeof($options));
//		$this->assertStringEndsWith('index', $options[0]);
//		$this->assertStringEndsWith('foo', $options[1]);
//		$this->assertEquals($options, SectionPathUtilities::getSectionPathOptions('/foo', self::INDEX_NAME, false));
//		$this->assertEquals($options, SectionPathUtilities::getSectionPathOptions('/foo/', self::INDEX_NAME, false));
//	}
//
//	public function testGetViewPathOptionsTwoElementPath() {
//		$options = SectionPathUtilities::getSectionPathOptions('foo/bar', self::INDEX_NAME, self::FALLBACK_NAME);
//		$this->assertEquals(5, sizeof($options));
//		$this->assertStringEndsWith('index', $options[0]);
//		$this->assertStringEndsWith('bar', $options[1]);
//		for ($i=2; $i<sizeof($options); $i++) {
//			$this->assertStringEndsWith('fallback', $options[$i], 'getViewPathOptions() with two element path returns correct value in position ' . $i);
//		}
//		$this->assertEquals($options, SectionPathUtilities::getSectionPathOptions('/foo/bar', self::INDEX_NAME, self::FALLBACK_NAME));
//		$this->assertEquals($options, SectionPathUtilities::getSectionPathOptions('/foo/bar/', self::INDEX_NAME, self::FALLBACK_NAME));
//	}
//
//	public function testGetViewPathOptionsTwoElementPathNoFallback() {
//		$options = SectionPathUtilities::getSectionPathOptions('foo/bar', self::INDEX_NAME, false);
//		$this->assertEquals(2, sizeof($options));
//		$this->assertStringEndsWith('index', $options[0]);
//		$this->assertStringEndsWith('bar', $options[1]);
//		$this->assertEquals($options, SectionPathUtilities::getSectionPathOptions('/foo/bar', self::INDEX_NAME, false));
//		$this->assertEquals($options, SectionPathUtilities::getSectionPathOptions('/foo/bar/', self::INDEX_NAME, false));
//	}
//
//	public function testGetViewPathOptionsThreeElementPath() {
//		$options = SectionPathUtilities::getSectionPathOptions('foo/bar/xyzzy', self::INDEX_NAME, self::FALLBACK_NAME);
//		$this->assertEquals(6, sizeof($options));
//		$this->assertStringEndsWith('index', $options[0]);
//		$this->assertStringEndsWith('xyzzy', $options[1]);
//		for ($i=2; $i<sizeof($options); $i++) {
//			$this->assertStringEndsWith('fallback', $options[$i], 'getViewPathOptions() with three element path returns correct value in position ' . $i);
//		}
//		$this->assertEquals($options, SectionPathUtilities::getSectionPathOptions('/foo/bar/xyzzy', self::INDEX_NAME, self::FALLBACK_NAME));
//		$this->assertEquals($options, SectionPathUtilities::getSectionPathOptions('/foo/bar/xyzzy/', self::INDEX_NAME, self::FALLBACK_NAME));
//	}
//
//	public function testGetViewPathOptionsThreeElementPathNoFallback() {
//		$options = SectionPathUtilities::getSectionPathOptions('foo/bar/xyzzy', self::INDEX_NAME, false);
//		$this->assertEquals(2, sizeof($options));
//		$this->assertStringEndsWith('index', $options[0]);
//		$this->assertStringEndsWith('xyzzy', $options[1]);
//		$this->assertEquals($options, SectionPathUtilities::getSectionPathOptions('/foo/bar/xyzzy', self::INDEX_NAME, false));
//		$this->assertEquals($options, SectionPathUtilities::getSectionPathOptions('/foo/bar/xyzzy/', self::INDEX_NAME, false));
//	}

//}
