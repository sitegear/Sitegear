<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Google;

use Sitegear\Base\Module\AbstractConfigurableModule;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Util\LoggerRegistry;

/**
 * Sitegear Google integration module - contains commands and components to assist with integration of Google services,
 * including analytics and maps.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class GoogleModule extends AbstractConfigurableModule {

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Google Integrations';
	}

	//-- Component Target Controller Methods --------------------

	/**
	 * Write JavaScript for Google Analytics.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param string|null $apiKey The API key, or null to use the value from the config.
	 * @param array|null Additional calls to the GA queue.
	 *
	 * @return boolean False to prevent output in analytics=disabled environments.
	 */
	public function analyticsComponent(ViewInterface $view, $apiKey=null, $additionalCalls=null) {
		LoggerRegistry::debug('GoogleModule::analyticsComponent');
		if ($this->config('analytics.enabled')) {
			$view['api-key'] = is_null($apiKey) ? $this->config('analytics.api.key') : $apiKey;
			$view['additional-calls'] = is_null($additionalCalls) ? $this->config('analytics.additional-calls') : $additionalCalls;
			return null;
		} else {
			return false;
		}
	}

	/**
	 * Write JavaScript for a Google Maps instance with specified coordinates and markers.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param string $selector
	 * @param array $initialView
	 * @param string|null $apiKey
	 * @param array $markers
	 * @param boolean $autoShowInfoWindow
	 * @param array $options
	 *
	 * @return void
	 */
	public function mapComponent(ViewInterface $view, $selector, array $initialView, $apiKey=null, array $markers=null, $autoShowInfoWindow=false, $options=null) {
		LoggerRegistry::debug('GoogleModule::mapComponent');
		$view['selector'] = $selector;
		if (empty($apiKey)) {
			$apiKey = $this->config('maps.api.key');
		}
		// TODO Allow other parameters to be specified in config / arguments
		$view['apiScriptUrl'] = sprintf('http://%s%s?sensor=true&amp;libraries=geometry&amp;key=%s', $this->config('maps.api.host'), $this->config('maps.api.path'), $apiKey);
		$view['initialView'] = $initialView;
		$view['markers'] = $markers ?: array();
		$defaultOptions = array(
			'map' => array(),
			'marker' => array(),
			'infoWindow' => array(),
		);
		$view['autoShowInfoWindow'] = $autoShowInfoWindow;
		$view['options'] = array_merge($defaultOptions, $options ?: array());
	}

	//-- Public Methods --------------------

	/**
	 * Geocode the given address using the Google Maps API.
	 *
	 * @param string $address Address to geocode.  Should be as complete as possible for an accurate result.  This
	 *   string is passed unmodified (except url-encoding) to the Maps API.
	 *
	 * @return array Array containing 'latitude', 'longitude' and 'altitude' properties.
	 *
	 * @throws \RuntimeException If the address cannot be geo-coding due to missing response data.
	 */
	public function geocodeLocation($address) {
		LoggerRegistry::debug('GoogleModule geocoding location');
		$key = $this->config('maps.account-key');
		$url = sprintf('http://%s/maps/geo?output=json&key=%s&q=%s', $this->config('maps.host'), $key, urlencode($address));
		$data = json_decode(file_get_contents($url), true);
		if (empty($data)) {
			throw new \RuntimeException(sprintf('Could not geocode location "%s", empty geocode response', $address));
		}
		if (!isset($data['Status']) || !isset($data['Status']['code'])) {
			throw new \RuntimeException(sprintf('Could not geocode location "%s", invalid geocode response structure', $address));
		}
		if ($data['Status']['code'] != '200') {
			throw new \RuntimeException(sprintf('Could not geocode location "%s", geocode response status indicates an error', $address));
		}
		if (!isset($data['Placemark']) || empty($data['Placemark']) || !is_array($data['Placemark'])) {
			throw new \RuntimeException(sprintf('Could not geocode location "%s", no location found by geocoder', $address));
		}
		$coordinates = $data['Placemark'][0]['Point']['coordinates'];
		return array(
			'latitude' => $coordinates[1],
			'longitude' => $coordinates[0],
			'altitude' => $coordinates[2]
		);
	}

}
