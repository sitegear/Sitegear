<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Google;

use Sitegear\Util\TypeUtilities;
use Sitegear\View\ViewInterface;
use Sitegear\Module\AbstractSitegearModule;
use Sitegear\Util\LoggerRegistry;

/**
 * Sitegear Google integration module - contains commands and components to assist with integration of Google services,
 * including analytics and maps.
 *
 * @method \Sitegear\Engine\SitegearEngine getEngine()
 */
class GoogleModule extends AbstractSitegearModule {

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getDisplayName() {
		return 'Google Integrations';
	}

	//-- Component Target Controller Methods --------------------

	/**
	 * Write JavaScript for Google Analytics.
	 *
	 * @param \Sitegear\View\ViewInterface $view
	 * @param string|null $apiKey The API key, or null to use the value from the config.
	 * @param array|null Additional calls to the GA queue.
	 *
	 * @return boolean False to prevent output in analytics=disabled environments.
	 */
	public function analyticsComponent(ViewInterface $view, $apiKey=null, $additionalCalls=null) {
		LoggerRegistry::debug(sprintf('GoogleModule::analyticsComponent([view], %s, %s)', $apiKey, TypeUtilities::describe($additionalCalls)));
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
	 * @param \Sitegear\View\ViewInterface $view
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
		LoggerRegistry::debug(sprintf('GoogleModule::mapComponent([view], %s, %s, %s, %s, %s, %s)', $selector, $initialView, $apiKey, TypeUtilities::describe($markers), $autoShowInfoWindow ? 'true' : 'false', TypeUtilities::describe($options)));
		$view['selector'] = $selector;
		if (empty($apiKey)) {
			$apiKey = $this->config('maps.api.key');
		}
		// TODO Allow other parameters to be specified in config / arguments
		$view['apiScriptUrl'] = sprintf('http://%s/maps/api/js?sensor=true&amp;libraries=geometry&amp;key=%s', $this->config('maps.api.host'), $this->config('maps.api.path'), $apiKey);
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
		LoggerRegistry::debug(sprintf('GoogleModule::geocodeLocation(%s)', $address));
		$url = sprintf('http://%s/maps/api/geocode/json?address=%s&sensor=false', $this->config('maps.api.host'), urlencode($address));
		$data = json_decode(file_get_contents($url), true);
		if (!isset($data['status'])) {
			throw new \RuntimeException(sprintf('Could not geocode location "%s", invalid geocode response structure', $address));
		}
		if ($data['status'] !== 'OK') {
			throw new \RuntimeException(sprintf('Could not geocode location "%s", geocode response status indicates an error: %s', $address, $data['status']));
		}
		if (!isset($data['results']) || !isset($data['results'][0]) || !isset($data['results'][0]['geometry']) || !isset($data['results'][0]['geometry']['location'])) {
			throw new \RuntimeException(sprintf('Could not geocode location "%s", geocode response contains no results', $address));
		}
		$coordinates = $data['results'][0]['geometry']['location'];
		return array(
			'latitude' => $coordinates['lat'],
			'longitude' => $coordinates['lng']
		);
	}

}
