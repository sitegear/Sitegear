<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

// This namespace intentionally does not match the file path
namespace Sitegear;

use Sitegear\Engine\SitegearEngine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class TestEngine extends SitegearEngine {

	protected function createSession(Request $request) {
		$session = new Session();
		$request->setSession($session);
		$session->setName('sitegear.test');
		return $session;
	}

}
