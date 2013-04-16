<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Renderer\Registry;

use Sitegear\AbstractSitegearTestCase;

use Symfony\Component\HttpFoundation\Request;

class SimpleRendererRegistryTest extends AbstractSitegearTestCase {

	const MOCK_CONTENT_RENDERER = '\\Mock\\MockRenderer';

	/**
	 * @var \Sitegear\View\Renderer\Registry\RendererRegistry
	 */
	private $registry;

	public function setUp() {
		parent::setUp();
		$this->registry = new RendererRegistry();
	}

	public function testGetInstanceInvariance() {
		$this->assertSame($this->registry, $this->registry);
	}

	public function testRegistrationCycle() {
		$this->assertFalse($this->registry->isRegistered(self::MOCK_CONTENT_RENDERER));
		$this->registry->register(self::MOCK_CONTENT_RENDERER);
		$this->assertTrue($this->registry->isRegistered(self::MOCK_CONTENT_RENDERER));
		$this->registry->deregister(self::MOCK_CONTENT_RENDERER);
		$this->assertFalse($this->registry->isRegistered(self::MOCK_CONTENT_RENDERER));
	}

	/**
	 * @expectedException \DomainException
	 */
	public function testRegisterClassNotExists() {
		$this->registry->register('\\This\\Class\\Does\\Not\\Exist');
	}

	/**
	 * @expectedException \DomainException
	 */
	public function testRegisterClassNotImplementsRenderer() {
		$this->registry->register('\\StdClass');
	}

	public function testRendering() {
		$this->assertFalse($this->registry->canRender('/anything/for/mock/renderer', $this->getMock('\\Sitegear\\View\\ViewInterface')));
		$this->assertFalse($this->registry->canRender('/do/not/render', $this->getMock('\\Sitegear\\View\\ViewInterface')));
		$this->assertNull($this->registry->render('/anything/for/mock/renderer', $this->getMock('\\Sitegear\\View\\ViewInterface')));
		$this->assertNull($this->registry->render('/do/not/render', $this->getMock('\\Sitegear\\View\\ViewInterface')));
		$this->registry->register(self::MOCK_CONTENT_RENDERER);
		$this->assertTrue($this->registry->canRender('/anything/for/mock/renderer', $this->getMock('\\Sitegear\\View\\ViewInterface')));
		$this->assertFalse($this->registry->canRender('/do/not/render', $this->getMock('\\Sitegear\\View\\ViewInterface')));
		$this->assertEquals('"/anything/for/mock/renderer" rendered by MockRenderer', $this->registry->render('/anything/for/mock/renderer', $this->getMock('\\Sitegear\\View\\ViewInterface')));
		$this->assertNull($this->registry->render('/do/not/render', $this->getMock('\\Sitegear\\View\\ViewInterface')));
	}

}
