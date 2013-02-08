<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

use Sitegear\AbstractSitegearTestCase;

class NameUtilitiesTest extends AbstractSitegearTestCase {

	public function testMatchesCamelCase() {
		$this->assertTrue(NameUtilities::matchesCamelCase('camelCaseString'));
		$this->assertFalse(NameUtilities::matchesCamelCase('StudlyCapsString'));
		$this->assertFalse(NameUtilities::matchesCamelCase('dashed-lower-string'));
		$this->assertFalse(NameUtilities::matchesCamelCase('underscore_lower_string'));
		$this->assertFalse(NameUtilities::matchesCamelCase('Underscore_Caps_String'));
		$this->assertFalse(NameUtilities::matchesCamelCase('lower case string'));
		$this->assertFalse(NameUtilities::matchesCamelCase('Title Case String'));
		$this->assertFalse(NameUtilities::matchesCamelCase('This One Isn\'t Easy - A Test Case @ sitegear.org'));
	}

	public function testMatchesStudlyCaps() {
		$this->assertFalse(NameUtilities::matchesStudlyCaps('camelCaseString'));
		$this->assertTrue(NameUtilities::matchesStudlyCaps('StudlyCapsString'));
		$this->assertFalse(NameUtilities::matchesStudlyCaps('dashed-lower-string'));
		$this->assertFalse(NameUtilities::matchesStudlyCaps('underscore_lower_string'));
		$this->assertFalse(NameUtilities::matchesStudlyCaps('Underscore_Caps_String'));
		$this->assertFalse(NameUtilities::matchesStudlyCaps('lower case string'));
		$this->assertFalse(NameUtilities::matchesStudlyCaps('Title Case String'));
		$this->assertFalse(NameUtilities::matchesStudlyCaps('This One Isn\'t Easy - A Test Case @ sitegear.org'));
	}

	public function testMatchesDashedLower() {
		$this->assertFalse(NameUtilities::matchesDashedLower('camelCaseString'));
		$this->assertFalse(NameUtilities::matchesDashedLower('StudlyCapsString'));
		$this->assertTrue(NameUtilities::matchesDashedLower('dashed-lower-string'));
		$this->assertFalse(NameUtilities::matchesDashedLower('underscore_lower_string'));
		$this->assertFalse(NameUtilities::matchesDashedLower('Underscore_Caps_String'));
		$this->assertFalse(NameUtilities::matchesDashedLower('lower case string'));
		$this->assertFalse(NameUtilities::matchesDashedLower('Title Case String'));
		$this->assertFalse(NameUtilities::matchesDashedLower('This One Isn\'t Easy - A Test Case @ sitegear.org'));
	}

	public function testMatchesUnderscoreLower() {
		$this->assertFalse(NameUtilities::matchesUnderscoreLower('camelCaseString'));
		$this->assertFalse(NameUtilities::matchesUnderscoreLower('StudlyCapsString'));
		$this->assertFalse(NameUtilities::matchesUnderscoreLower('dashed-lower-string'));
		$this->assertTrue(NameUtilities::matchesUnderscoreLower('underscore_lower_string'));
		$this->assertFalse(NameUtilities::matchesUnderscoreLower('Underscore_Caps_String'));
		$this->assertFalse(NameUtilities::matchesUnderscoreLower('lower case string'));
		$this->assertFalse(NameUtilities::matchesUnderscoreLower('Title Case String'));
		$this->assertFalse(NameUtilities::matchesUnderscoreLower('This One Isn\'t Easy - A Test Case @ sitegear.org'));
	}

	public function testMatchesUnderscoreCaps() {
		$this->assertFalse(NameUtilities::matchesUnderscoreCaps('camelCaseString'));
		$this->assertFalse(NameUtilities::matchesUnderscoreCaps('StudlyCapsString'));
		$this->assertFalse(NameUtilities::matchesUnderscoreCaps('dashed-lower-string'));
		$this->assertFalse(NameUtilities::matchesUnderscoreCaps('underscore_lower_string'));
		$this->assertTrue(NameUtilities::matchesUnderscoreCaps('Underscore_Caps_String'));
		$this->assertFalse(NameUtilities::matchesUnderscoreCaps('lower case string'));
		$this->assertFalse(NameUtilities::matchesUnderscoreCaps('Title Case String'));
		$this->assertFalse(NameUtilities::matchesUnderscoreCaps('This One Isn\'t Easy - A Test Case @ sitegear.org'));
	}

	public function testMatchesLowerCase() {
		$this->assertFalse(NameUtilities::matchesLowerCase('camelCaseString'));
		$this->assertFalse(NameUtilities::matchesLowerCase('StudlyCapsString'));
		$this->assertFalse(NameUtilities::matchesLowerCase('dashed-lower-string'));
		$this->assertFalse(NameUtilities::matchesLowerCase('underscore_lower_string'));
		$this->assertFalse(NameUtilities::matchesLowerCase('Underscore_Caps_String'));
		$this->assertTrue(NameUtilities::matchesLowerCase('lower case string'));
		$this->assertFalse(NameUtilities::matchesLowerCase('Title Case String'));
		$this->assertFalse(NameUtilities::matchesLowerCase('This One Isn\'t Easy - A Test Case @ sitegear.org'));
	}

	public function testMatchesTitleCase() {
		$this->assertFalse(NameUtilities::matchesTitleCase('camelCaseString'));
		$this->assertFalse(NameUtilities::matchesTitleCase('StudlyCapsString'));
		$this->assertFalse(NameUtilities::matchesTitleCase('dashed-lower-string'));
		$this->assertFalse(NameUtilities::matchesTitleCase('underscore_lower_string'));
		$this->assertFalse(NameUtilities::matchesTitleCase('Underscore_Caps_String'));
		$this->assertFalse(NameUtilities::matchesTitleCase('lower case string'));
		$this->assertTrue(NameUtilities::matchesTitleCase('Title Case String'));
		$this->assertFalse(NameUtilities::matchesTitleCase('This One Isn\'t Easy - A Test Case @ sitegear.org'));
	}

	public function testConvertToCamelCase() {
		$this->assertEquals('fromCamelCase', NameUtilities::convertToCamelCase('fromCamelCase'));
		$this->assertEquals('fromStudlyCaps', NameUtilities::convertToCamelCase('FromStudlyCaps'));
		$this->assertEquals('fromDashedLower', NameUtilities::convertToCamelCase('from-dashed-lower'));
		$this->assertEquals('fromUnderscoreLower', NameUtilities::convertToCamelCase('from-underscore-lower'));
		$this->assertEquals('fromUnderscoreCaps', NameUtilities::convertToCamelCase('From_Underscore_Caps'));
		$this->assertEquals('fromLowerCase', NameUtilities::convertToCamelCase('from lower case'));
		$this->assertEquals('fromTitleCase', NameUtilities::convertToCamelCase('From Title Case'));
		$this->assertEquals('thisOneIsntEasyATestCaseSitegearOrg', NameUtilities::convertToCamelCase('This One Isn\'t Easy - A Test Case @ sitegear.org'));
	}

	public function testConvertToStudlyCaps() {
		$this->assertEquals('FromCamelCase', NameUtilities::convertToStudlyCaps('fromCamelCase'));
		$this->assertEquals('FromStudlyCaps', NameUtilities::convertToStudlyCaps('FromStudlyCaps'));
		$this->assertEquals('FromDashedLower', NameUtilities::convertToStudlyCaps('from-dashed-lower'));
		$this->assertEquals('FromUnderscoreLower', NameUtilities::convertToStudlyCaps('from-underscore-lower'));
		$this->assertEquals('FromUnderscoreCaps', NameUtilities::convertToStudlyCaps('From_Underscore_Caps'));
		$this->assertEquals('FromLowerCase', NameUtilities::convertToStudlyCaps('from lower case'));
		$this->assertEquals('FromTitleCase', NameUtilities::convertToStudlyCaps('From Title Case'));
		$this->assertEquals('ThisOneIsntEasyATestCaseSitegearOrg', NameUtilities::convertToStudlyCaps('This One Isn\'t Easy - A Test Case @ sitegear.org'));
	}

	public function testConvertToDashedLower() {
		$this->assertEquals('from-camel-case', NameUtilities::convertToDashedLower('fromCamelCase'));
		$this->assertEquals('from-studly-caps', NameUtilities::convertToDashedLower('FromStudlyCaps'));
		$this->assertEquals('from-dashed-lower', NameUtilities::convertToDashedLower('from-dashed-lower'));
		$this->assertEquals('from-underscore-lower', NameUtilities::convertToDashedLower('from-underscore-lower'));
		$this->assertEquals('from-underscore-caps', NameUtilities::convertToDashedLower('From_Underscore_Caps'));
		$this->assertEquals('from-lower-case', NameUtilities::convertToDashedLower('from lower case'));
		$this->assertEquals('from-title-case', NameUtilities::convertToDashedLower('From Title Case'));
		$this->assertEquals('this-one-isnt-easy-a-test-case-sitegear-org', NameUtilities::convertToDashedLower('This One Isn\'t Easy - A Test Case @ sitegear.org'));
	}

	public function testConvertToUnderscoreLower() {
		$this->assertEquals('from_camel_case', NameUtilities::convertToUnderscoreLower('fromCamelCase'));
		$this->assertEquals('from_studly_caps', NameUtilities::convertToUnderscoreLower('FromStudlyCaps'));
		$this->assertEquals('from_dashed_lower', NameUtilities::convertToUnderscoreLower('from-dashed-lower'));
		$this->assertEquals('from_underscore_lower', NameUtilities::convertToUnderscoreLower('from_underscore_lower'));
		$this->assertEquals('from_underscore_caps', NameUtilities::convertToUnderscoreLower('From_Underscore_Caps'));
		$this->assertEquals('from_lower_case', NameUtilities::convertToUnderscoreLower('from lower case'));
		$this->assertEquals('from_title_case', NameUtilities::convertToUnderscoreLower('From Title Case'));
		$this->assertEquals('this_one_isnt_easy_a_test_case_sitegear_org', NameUtilities::convertToUnderscoreLower('This One Isn\'t Easy - A Test Case @ sitegear.org'));
	}

	public function testConvertToUnderscoreCaps() {
		$this->assertEquals('From_Camel_Case', NameUtilities::convertToUnderscoreCaps('fromCamelCase'));
		$this->assertEquals('From_Studly_Caps', NameUtilities::convertToUnderscoreCaps('FromStudlyCaps'));
		$this->assertEquals('From_Dashed_Lower', NameUtilities::convertToUnderscoreCaps('from-dashed-lower'));
		$this->assertEquals('From_Underscore_Lower', NameUtilities::convertToUnderscoreCaps('from_underscore_lower'));
		$this->assertEquals('From_Underscore_Caps', NameUtilities::convertToUnderscoreCaps('From_Underscore_Caps'));
		$this->assertEquals('From_Lower_Case', NameUtilities::convertToUnderscoreCaps('from lower case'));
		$this->assertEquals('From_Title_Case', NameUtilities::convertToUnderscoreCaps('From Title Case'));
		$this->assertEquals('This_One_Isnt_Easy_A_Test_Case_Sitegear_Org', NameUtilities::convertToUnderscoreCaps('This One Isn\'t Easy - A Test Case @ sitegear.org'));
	}

	public function testConvertToLowerCase() {
		$this->assertEquals('from camel case', NameUtilities::convertToLowerCase('fromCamelCase'));
		$this->assertEquals('from studly caps', NameUtilities::convertToLowerCase('FromStudlyCaps'));
		$this->assertEquals('from dashed lower', NameUtilities::convertToLowerCase('from-dashed-lower'));
		$this->assertEquals('from underscore lower', NameUtilities::convertToLowerCase('from_underscore_lower'));
		$this->assertEquals('from underscore caps', NameUtilities::convertToLowerCase('From_Underscore_Caps'));
		$this->assertEquals('from lower case', NameUtilities::convertToLowerCase('from lower case'));
		$this->assertEquals('from title case', NameUtilities::convertToLowerCase('From Title Case'));
		$this->assertEquals('this one isnt easy a test case sitegear org', NameUtilities::convertToLowerCase('This One Isn\'t Easy - A Test Case @ sitegear.org'));
	}

	public function testConvertToTitleCase() {
		$this->assertEquals('From Camel Case', NameUtilities::convertToTitleCase('fromCamelCase'));
		$this->assertEquals('From Studly Caps', NameUtilities::convertToTitleCase('FromStudlyCaps'));
		$this->assertEquals('From Dashed Lower', NameUtilities::convertToTitleCase('from-dashed-lower'));
		$this->assertEquals('From Underscore Lower', NameUtilities::convertToTitleCase('from_underscore_lower'));
		$this->assertEquals('From Underscore Caps', NameUtilities::convertToTitleCase('From_Underscore_Caps'));
		$this->assertEquals('From Lower Case', NameUtilities::convertToTitleCase('from lower case'));
		$this->assertEquals('From Title Case', NameUtilities::convertToTitleCase('From Title Case'));
		$this->assertEquals('This One Isnt Easy A Test Case Sitegear Org', NameUtilities::convertToTitleCase('This One Isn\'t Easy - A Test Case @ sitegear.org'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidForm() {
		NameUtilities::convert('Anything', 123);
	}

}
