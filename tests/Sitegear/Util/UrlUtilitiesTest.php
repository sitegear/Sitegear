<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

use Sitegear\AbstractSitegearTestCase;

use Symfony\Component\HttpFoundation\Request;

class UrlUtilitiesTest extends AbstractSitegearTestCase {

	public function testGenerateLinkWithReturnUrl() {
		$this->assertEquals('some/page?return=return%2Fpage', UrlUtilities::generateLinkWithReturnUrl('some/page', 'return/page'));
		$this->assertEquals('some/page?return=http%3A%2F%2Fwebsite.com%2Fexternal%2Fpage', UrlUtilities::generateLinkWithReturnUrl('some/page', 'http://website.com/external/page'));
		$this->assertEquals('http://website.com/external/page?return=http%3A%2F%2Fbackhere.com%2Freturn%2Fpage', UrlUtilities::generateLinkWithReturnUrl('http://website.com/external/page', 'http://backhere.com/return/page'));
		$this->assertEquals('some/page?return=', UrlUtilities::generateLinkWithReturnUrl('some/page', null));
		$this->assertEquals('some/page?custom-return=return%2Fpage', UrlUtilities::generateLinkWithReturnUrl('some/page', 'return/page', 'custom-return'));
	}

	public function testGenerateLinkWithReturnUrlUsingRequestObjects() {
		$this->assertEquals('http://localhost/some/page?return=return%2Fpage', UrlUtilities::generateLinkWithReturnUrl(Request::create('/some/page'), 'return/page'));
		$this->assertEquals('some/page?return=http%3A%2F%2Flocalhost%2Freturn%2Fpage', UrlUtilities::generateLinkWithReturnUrl('some/page', Request::create('/return/page')));
		$this->assertEquals('http://localhost/some/page?return=http%3A%2F%2Flocalhost%2Freturn%2Fpage', UrlUtilities::generateLinkWithReturnUrl(Request::create('/some/page'), Request::create('/return/page')));
	}

	public function testGenerateLinkWithReturnUrlWithQueryParameters() {
		$this->assertEquals('some/page?q=something&return=return%2Fpage', UrlUtilities::generateLinkWithReturnUrl('some/page?q=something', 'return/page'));
		$this->assertEquals('some/page?q=something&foo=bar&return=return%2Fpage', UrlUtilities::generateLinkWithReturnUrl('some/page?q=something&foo=bar', 'return/page'));
		$this->assertEquals('some/page?return=return%2Fpage%3Fq%3Dquery', UrlUtilities::generateLinkWithReturnUrl('some/page', 'return/page?q=query'));
		$this->assertEquals('some/page?return=return%2Fpage%3Fq%3Dquery%26x%3Dy', UrlUtilities::generateLinkWithReturnUrl('some/page', 'return/page?q=query&x=y'));
		$this->assertEquals('some/page?q=something&return=return%2Fpage%3Fq%3Dquery', UrlUtilities::generateLinkWithReturnUrl('some/page?q=something', 'return/page?q=query'));
		$this->assertEquals('some/page?q=something&foo=bar&return=return%2Fpage%3Fq%3Dquery%26x%3Dy', UrlUtilities::generateLinkWithReturnUrl('some/page?q=something&foo=bar', 'return/page?q=query&x=y'));
	}

	public function testGetReturnUrl() {
		$this->assertEquals('http://localhost/return/page', UrlUtilities::getReturnUrl('some/page?return=return%2Fpage'));
		$this->assertEquals('http://website.com/external/page', UrlUtilities::getReturnUrl('some/page?return=http%3A%2F%2Fwebsite.com%2Fexternal%2Fpage'));
		$this->assertEquals('http://backhere.com/return/page', UrlUtilities::getReturnUrl('http://website.com/external/page?return=http%3A%2F%2Fbackhere.com%2Freturn%2Fpage'));
		$this->assertEmpty(UrlUtilities::getReturnUrl('some/page?return='));
		$this->assertEmpty(UrlUtilities::getReturnUrl('some/page'));
		$this->assertEquals('http://localhost/return/page', UrlUtilities::getReturnUrl('some/page?custom-return=return%2Fpage', 'custom-return'));
	}

	public function testGetReturnUrlUsingRequestObjectsAbsolute() {
		$this->assertEquals('http://localhost/return/page', UrlUtilities::getReturnUrl(Request::create('some/page?return=return%2Fpage')));
	}

	public function testGetReturnUrlUsingRequestObjectsNotAbsolute() {
		$this->assertEquals('return/page', UrlUtilities::getReturnUrl(Request::create('some/page?return=return%2Fpage'), null, null, false));
	}

	public function testGetReturnUrlWithQueryParametersAbsolute() {
		$this->assertEquals('http://localhost/return/page', UrlUtilities::getReturnUrl('some/page?q=something&return=return%2Fpage'));
		$this->assertEquals('http://localhost/return/page', UrlUtilities::getReturnUrl('some/page?q=something&foo=bar&return=return%2Fpage'));
		$this->assertEquals('http://localhost/return/page?q=query', UrlUtilities::getReturnUrl('some/page?return=return%2Fpage%3Fq%3Dquery'));
		$this->assertEquals('http://localhost/return/page?q=query&x=y', UrlUtilities::getReturnUrl('some/page?return=return%2Fpage%3Fq%3Dquery%26x%3Dy'));
		$this->assertEquals('http://localhost/return/page?q=query', UrlUtilities::getReturnUrl('some/page?q=something&return=return%2Fpage%3Fq%3Dquery'));
		$this->assertEquals('http://localhost/return/page?q=query&x=y', UrlUtilities::getReturnUrl('some/page?q=something&foo=bar&return=return%2Fpage%3Fq%3Dquery%26x%3Dy'));
	}

	public function testGetReturnUrlWithQueryParametersNotAbsolute() {
		$this->assertEquals('return/page', UrlUtilities::getReturnUrl('some/page?q=something&return=return%2Fpage', null, null, false));
		$this->assertEquals('return/page', UrlUtilities::getReturnUrl('some/page?q=something&foo=bar&return=return%2Fpage', null, null, false));
		$this->assertEquals('return/page?q=query', UrlUtilities::getReturnUrl('some/page?return=return%2Fpage%3Fq%3Dquery', null, null, false));
		$this->assertEquals('return/page?q=query&x=y', UrlUtilities::getReturnUrl('some/page?return=return%2Fpage%3Fq%3Dquery%26x%3Dy', null, null, false));
		$this->assertEquals('return/page?q=query', UrlUtilities::getReturnUrl('some/page?q=something&return=return%2Fpage%3Fq%3Dquery', null, null, false));
		$this->assertEquals('return/page?q=query&x=y', UrlUtilities::getReturnUrl('some/page?q=something&foo=bar&return=return%2Fpage%3Fq%3Dquery%26x%3Dy', null, null, false));
	}

	public function testWildcardUrlToRegex() {
		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('?'), '/'));
		$this->assertEquals(0, preg_match(UrlUtilities::compileWildcardUrl('?'), '/foo'));
		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('??'), '/'));
		$this->assertEquals(0, preg_match(UrlUtilities::compileWildcardUrl('??'), '/foo'));
		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('???'), '/'));
		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('???'), '/foo'));
		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('????'), '/'));
		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('????'), '/foo'));

		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('/root*'), '/root'));
		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('/root*'), '/root/'));
		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('/root*'), '/root/foo'));
		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('/root*'), '/root/foo/bar'));
		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('/root*'), '/root-with-suffix'));
		$this->assertEquals(0, preg_match(UrlUtilities::compileWildcardUrl('/root*'), '/'));
		$this->assertEquals(0, preg_match(UrlUtilities::compileWildcardUrl('/root*'), '/foo'));
		$this->assertEquals(0, preg_match(UrlUtilities::compileWildcardUrl('/root*'), '/foo/bar'));

		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('/'), '/'));
		$this->assertEquals(0, preg_match(UrlUtilities::compileWildcardUrl('/'), '/foo/bar'));
		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('/foo/bar'), '/foo/bar'));
		$this->assertEquals(0, preg_match(UrlUtilities::compileWildcardUrl('/foo/bar'), '/'));

		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('!'), '/'));
		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('!'), '/foo'));
		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('!'), '/foo/bar'));

		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('/root!'), '/root'));
		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('/root!'), '/root/'));
		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('/root!'), '/root/foo'));
		$this->assertEquals(1, preg_match(UrlUtilities::compileWildcardUrl('/root!'), '/root/foo/bar'));
		$this->assertEquals(0, preg_match(UrlUtilities::compileWildcardUrl('/root!'), '/'));
		$this->assertEquals(0, preg_match(UrlUtilities::compileWildcardUrl('/root!'), '/foo'));
		$this->assertEquals(0, preg_match(UrlUtilities::compileWildcardUrl('/root!'), '/foo/bar'));
	}

}
