<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Session;

use Sitegear\AbstractSitegearTestCase;
use Sitegear\Module\Google\GoogleModule;
use Sitegear\TestEngine;

use Symfony\Component\HttpFoundation\Request;

class GoogleModuleTest extends AbstractSitegearTestCase {

	/**
	 * @var GoogleModule
	 */
	private $module;

	public function setUp() {
		parent::setUp();
		$engine = new TestEngine($this->fixtures(), 'test');
		$engine->configure()->start(Request::create('/'));
		$this->module = new GoogleModule($engine);
		$this->module->configure()->start();
	}

	public function testGeocodeLocation() {
		$addresses = array(
			array(
				'address' => '1 St Georges Tce, Perth, WA, Australia',
				'latitude' => -31.9577874,
				'longitude' => 115.864731
			),
			array(
				'address' => '10 Downing Street, London, United Kingdom',
				'latitude' => 51.5033549,
				'longitude' => -0.1275645
			)
		);
		foreach ($addresses as $address) {
			$geocodeResult = $this->module->geocodeLocation($address['address']);
			$this->assertEquals($address['latitude'], $geocodeResult['latitude']);
			$this->assertEquals($address['longitude'], $geocodeResult['longitude']);
		}
	}

}
