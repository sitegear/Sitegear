<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

use Sitegear\AbstractSitegearTestCase;

class JsonFormatterTest extends AbstractSitegearTestCase {

	/**
	 * @var JsonFormatter
	 */
	public $formatter;

	public function setUp() {
		parent::setUp();
		$this->formatter = new JsonFormatter();
	}

	public function testSetGetIndentation() {
		$indentationOverride = '    ';
		$this->formatter->setIndentation($indentationOverride);
		$this->assertEquals($indentationOverride, $this->formatter->getIndentation());
	}

	public function testSetGetLineBreak() {
		$lineBreakOverride = "\r\n";
		$this->formatter->setLineBreak($lineBreakOverride);
		$this->assertEquals($lineBreakOverride, $this->formatter->getLineBreak());
	}

	public function testSetGetInitialIndentLevel() {
		$initialIndentLevelOverride = 5;
		$this->formatter->setInitialIndentLevel($initialIndentLevelOverride);
		$this->assertEquals($initialIndentLevelOverride, $this->formatter->getInitialIndentLevel());
	}

	public function testSetGetIgnorePrettyPrint() {
		$ignorePrettyPrintOverride = true;
		$this->formatter->setIgnorePrettyPrint($ignorePrettyPrintOverride);
		$this->assertEquals($ignorePrettyPrintOverride, $this->formatter->getIgnorePrettyPrint());
	}

	public function testFormatJson() {
		$originalData = file_get_contents($this->fixtures() . 'testdata.json');
		$decodedOriginalData = json_decode($originalData, true);
		$formattedData = $this->formatter->formatJson($originalData);
		$decodedFormattedData = json_decode($formattedData, true);
		$this->assertEquals($decodedOriginalData, $decodedFormattedData);
	}

	public function testFormatJsonIgnorePrettyPrint() {
		$originalData = file_get_contents($this->fixtures() . 'testdata.json');
		$decodedOriginalData = json_decode($originalData, true);
		$this->formatter->setIgnorePrettyPrint(true);
		$formattedData = $this->formatter->formatJson($originalData);
		$decodedFormattedData = json_decode($formattedData, true);
		$this->assertEquals($decodedOriginalData, $decodedFormattedData);
	}

	public function testFormatJsonCustomIndentationLevel() {
		$originalData = file_get_contents($this->fixtures() . 'testdata.json');
		$decodedOriginalData = json_decode($originalData, true);
		$this->formatter->setIgnorePrettyPrint(true);
		$this->formatter->setInitialIndentLevel(5);
		$formattedData = $this->formatter->formatJson($originalData);
		$decodedFormattedData = json_decode($formattedData, true);
		$this->assertEquals($decodedOriginalData, $decodedFormattedData);
	}

	public function testFormatJsonCustomFormatStrings() {
		$originalData = file_get_contents($this->fixtures() . 'testdata.json');
		$decodedOriginalData = json_decode($originalData, true);
		$this->formatter->setIgnorePrettyPrint(true);
		$this->formatter->setIndentation('    ');
		$this->formatter->setLineBreak("\r\n");
		$formattedData = $this->formatter->formatJson($originalData);
		$decodedFormattedData = json_decode($formattedData, true);
		$this->assertEquals($decodedOriginalData, $decodedFormattedData);
	}

	public function testFormatJsonInvalidCustomFormatStrings() {
		$originalData = file_get_contents($this->fixtures() . 'testdata.json');
		$this->formatter->setIgnorePrettyPrint(true);
		$this->formatter->setIndentation('junk');
		$this->formatter->setIndentation("foo\n");
		$formattedData = $this->formatter->formatJson($originalData);
		$this->assertNull(json_decode($formattedData, true));
	}

}
