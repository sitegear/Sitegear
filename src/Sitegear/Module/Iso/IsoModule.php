<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Iso;

use Sitegear\Core\Module\AbstractCoreModule;

/**
 * Modular implementation of relevant ISO standards.
 */
class IsoModule extends AbstractCoreModule {

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'ISO Standards';
	}

	//-- Public Methods --------------------

	/**
	 * Retrieve a key-value array of country codes mapped to their names.
	 *
	 * @param array|null $includedCodes Indexed array of codes to include in the result.
	 *
	 * @return array Key-value array with code keys and label values, of ISO-3166 countries, possibly filtered to
	 *   only include the specified codes.
	 */
	public function getIso3166CountryCodes(array $includedCodes=null) {
		$codes = $this->config('iso-3166-country-codes');
		if (!is_null($includedCodes)) {
			$includedCodes = array_fill_keys($includedCodes ?: array(), true);
			$codes = array_intersect_key($codes, $includedCodes);
		}
		return $codes;
	}

	/**
	 * Retrieve an array structure suitable for use in form generation containing country codes and names.
	 *
	 * @param string|null $blankValueLabel
	 * @param array|null $includedCodes Indexed array of codes to ignore.
	 *
	 * @return array Array of key-value arrays, each of which contains a 'value' and 'label' key, representing ISO-3166
	 *   countries, possibly filtered to only include the specified codes.
	 */
	public function getIso3166CountrySelectOptions($blankValueLabel=null, array $includedCodes=null) {
		$codes = $this->getIso3166CountryCodes($includedCodes);
		$codeOptions = array_map(function($code, $label) {
			return array(
				'value' => $code,
				'label' => $label
			);
		}, array_keys($codes), array_values($codes));
		if (!is_null($blankValueLabel)) {
			array_unshift($codeOptions, array(
				'value' => null,
				'label' => $blankValueLabel
			));
		}
		return $codeOptions;
	}

}
