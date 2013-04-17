<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

use Sitegear\AbstractSitegearTestCase;

class TokenGeneratorTest extends AbstractSitegearTestCase {

	public function testGenerateTokenDefaults() {
		$token = TokenGenerator::generateToken();
		$this->assertGreaterThanOrEqual(TokenGenerator::MIN_LENGTH_DEFAULT, strlen($token));
		$this->assertLessThanOrEqual(TokenGenerator::MAX_LENGTH_DEFAULT, strlen($token));
	}

	public function testGenerateTokenCustomMinMax() {
		$token = TokenGenerator::generateToken(4, 8);
		$this->assertGreaterThanOrEqual(4, strlen($token));
		$this->assertLessThanOrEqual(8, strlen($token));
	}

	public function testGenerateTokenCustomCharacterList() {
		$token = TokenGenerator::generateToken(null, null, 'abcdefghijklmnopqrstuvwx');
		$this->assertGreaterThanOrEqual(TokenGenerator::MIN_LENGTH_DEFAULT, strlen($token));
		$this->assertLessThanOrEqual(TokenGenerator::MAX_LENGTH_DEFAULT, strlen($token));
		$this->assertRegExp('/^[a-x]+$/', $token);
	}

	public function testGenerateTokenCustomMinMaxCharacterList() {
		$token = TokenGenerator::generateToken(4, 8, '1234567890');
		$this->assertGreaterThanOrEqual(4, strlen($token));
		$this->assertLessThanOrEqual(8, strlen($token));
		$this->assertRegExp('/^\\d+$/', $token);
	}

}
