<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Content;

use Sitegear\AbstractSitegearTestCase;
use Sitegear\Core\Module\Content\ContentModule;
use Sitegear\TestEngine;

use Symfony\Component\HttpFoundation\Request;

class ContentModuleTest extends AbstractSitegearTestCase {

	/**
	 * @var ContentModule
	 */
	private $module;

	public function setUp() {
		parent::setUp();
		$engine = new TestEngine($this->fixtures(), 'test');
		$engine->configure()->start(Request::create('/'));
		$this->module = new ContentModule($engine);
		$this->module->configure()->start();
	}

	public function testDefaultController() {
		$this->assertNull($this->module->defaultController());
	}

}
