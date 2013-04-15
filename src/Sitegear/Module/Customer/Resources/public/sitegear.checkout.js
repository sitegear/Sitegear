/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Checkout form script.
 */
(function($) {

	/**
	 * Toggle fields immediately without animation, and setup a change event to toggle with animation when any of the
	 * toggles are changed.
	 *
	 * @param $fieldset object
	 * @param $toggle object
	 * @param $additionalToggles object
	 */
	var initFields = function($fieldset, $toggle, $additionalToggles) {
		toggleFields($fieldset, $toggle.change(function() {
			toggleFields($fieldset, $toggle.is(':checked'), true);
		}).is(':checked'), false);
		$additionalToggles.change(function() {
			toggleFields($fieldset, $toggle.is(':checked'), true);
		});
	},

	/**
	 * Show or hide the fields based on the value of `show`, with animation or instant toggling based on the value of
	 * `animate`.
	 *
	 * @param $fieldset object
	 * @param show boolean
	 * @param animate boolean
	 */
	toggleFields = function($fieldset, show, animate) {
		var $fields = $fieldset.find('div.field');
		if (animate) {
			if (show) {
				$fields.slideDown(show);
			} else {
				$fields.slideUp(show);
			}
		} else {
			$fields.toggle(show);
		}
		$fields.find('input, select').attr('disabled', !show);
	};

	/**
	 * The "delivery address is different" checkbox: show/hide the delivery address fields.
	 * The "payment method" radio buttons: show/hide the credit card details fields.
	 */
	$(function() {
		initFields($('fieldset#sitegear-fieldset-delivery-address'), $('input#delivery-address-different-value-yes'), $());
		initFields($('fieldset#sitegear-fieldset-card-details'), $('input#payment-method-value-card'), $('input#payment-method-value-offline'));
	});

}(jQuery));
