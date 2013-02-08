<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

use Sitegear\AbstractSitegearTestCase;

use Symfony\Component\HttpKernel\Exception\FlattenException;

class HtmlUtilitiesTest extends AbstractSitegearTestCase {

	public function testDump() {
		$this->assertEmpty(HtmlUtilities::dump());
		$this->assertEquals("<pre></pre>\n", HtmlUtilities::dump(''));
		$this->assertEquals("<pre>Array\n(\n)\n</pre>\n", HtmlUtilities::dump(array()));
	}

	public function testDumpDataFromFile() {
		$testdata = HtmlUtilities::dump(json_decode(file_get_contents($this->fixtures() . 'testdata.json')));
		$this->assertStringMatchesFormat('%Afoobar%A', $testdata);
		$this->assertStringMatchesFormat('%Anested array%A', $testdata);
	}

	public function testDumpMultipleObjects() {
		$testdata = HtmlUtilities::dump(json_decode(file_get_contents($this->fixtures() . 'testdata.json')));
		$anotherObject = new \StdClass();
		$anotherObject->foo = 'bar';
		$anotherObject->baz = 'rah';
		$this->assertStringMatchesFormat('%A<hr%w/>%A', HtmlUtilities::dump($testdata, $anotherObject));
	}

	public function testAttributes() {
		$attributes = array(
			'id' => 'test-format-attributes',
			'class' => 'test-case',
			'onclick' => 'javascript:showStupidPopup(); return false;'
		);
		$formatted = HtmlUtilities::attributes($attributes);
		$this->assertStringMatchesFormat('%Sid="test-format-attributes"%S', $formatted);
		$this->assertStringMatchesFormat('%Sclass="test-case"%S', $formatted);
		$this->assertStringMatchesFormat('%Sonclick="javascript:showStupidPopup(); return false;"%S', $formatted);
	}

	public function testAttributesWithExcluded() {
		$attributes = array(
			'id' => 'test-format-attributes',
			'class' => 'test-case',
			'onclick' => 'javascript:showStupidPopup(); return false;'
		);
		$formatted = HtmlUtilities::attributes($attributes, array( 'onclick' ));
		$this->assertStringNotMatchesFormat('%Sonclick%S', $formatted);
	}

	public function testExcerpt() {
		$shortText = 'This is short text, it will be truncated with an excerpt length of 50 but not 100.';
		$shortTextExcerptLength50 = 'This is short text, it will be truncated with...';
		$longText = 'This is long text, it is longer than the default excerpt length and will therefore be truncated by calling excerpt() with an excerpt length argument of 50 or 100';
		$longTextExcerptLength50 = 'This is long text, it is longer than the...';
		$longTextExcerptLength100 = 'This is long text, it is longer than the default excerpt length and will therefore be truncated...';
		$this->assertEquals($shortTextExcerptLength50, HtmlUtilities::excerpt($shortText, 50));
		$this->assertEquals($shortText, HtmlUtilities::excerpt($shortText, 100));
		$this->assertEquals($longTextExcerptLength50, HtmlUtilities::excerpt($longText, 50));
		$this->assertEquals($longTextExcerptLength100, HtmlUtilities::excerpt($longText, 100));
	}

	public function testException() {
		$message = 'THIS IS THE ERROR MESSAGE';
		$e = new \Exception($message);
		$this->assertStringMatchesFormat("%A$message%A", HtmlUtilities::exception($e));
		$this->assertStringMatchesFormat("%A$message%A", HtmlUtilities::exception(FlattenException::create($e)));
	}

	public function testExceptionWithAdminDetails() {
		$e = new \Exception('ERROR MESSAGE');
		$this->assertStringMatchesFormat("%AName: Your Administrator%A", HtmlUtilities::exception($e, 'Your Administrator', 'admin@sitegear.org'));
		$this->assertStringMatchesFormat("%AEmail Address: admin@sitegear.org%A", HtmlUtilities::exception($e, 'Your Administrator', 'admin@sitegear.org'));
	}

}
