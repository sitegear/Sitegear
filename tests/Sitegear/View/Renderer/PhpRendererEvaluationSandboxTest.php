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

class PhpRendererEvaluationSandboxTest extends AbstractSitegearTestCase {

	public function testRender() {
		$scriptPath = $this->fixtures() . 'site/mock/test-phtml.phtml';
		$result = PhpRendererEvaluationSandbox::render($scriptPath, $this->getMock('\\Sitegear\\View\\ViewInterface'));
		$this->assertInternalType('string', $result);
		$this->assertNotEmpty($result);
	}

}
