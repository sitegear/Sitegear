<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Renderer;

use Sitegear\AbstractSitegearTestCase;

use Symfony\Component\HttpFoundation\Request;

class PhpRendererTest extends AbstractSitegearTestCase {

	/**
	 * @var PhpRenderer
	 */
	private $renderer;

	public function setUp() {
		parent::setUp();
		$this->renderer = new PhpRenderer();
	}

	public function testSupports() {
		$this->assertTrue($this->renderer->supports($this->fixtures() . 'site/mock/test-phtml'));
		$this->assertTrue($this->renderer->supports($this->fixtures() . 'site/mock/test-html-php'));
		$this->assertFalse($this->renderer->supports($this->fixtures() . 'site/mock/test-twig'));
		$this->assertFalse($this->renderer->supports($this->fixtures() . 'site/mock/test-non-existent'));
	}

	public function testRender() {
		$this->assertNotEmpty($this->renderer->render($this->fixtures() . 'site/mock/test-phtml', $this->getMock('\\Sitegear\\View\\ViewInterface')));
		$this->assertNotEmpty($this->renderer->render($this->fixtures() . 'site/mock/test-html-php', $this->getMock('\\Sitegear\\View\\ViewInterface')));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testRenderFileNotSupported() {
		$this->renderer->render($this->fixtures() . 'site/mock/test-twig', $this->getMock('\\Sitegear\\View\\ViewInterface'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testRenderFileNotExists() {
		$this->renderer->render($this->fixtures() . 'site/mock/test-non-existent', $this->getMock('\\Sitegear\\View\\ViewInterface'));
	}

}
