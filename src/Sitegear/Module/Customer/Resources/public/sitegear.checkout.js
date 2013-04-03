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
	 * Show or hide the fields based on the value of `show`, with animation or instant toggling based on the value of
	 * `animate`.
	 *
	 * @param show boolean
	 * @param animate boolean
	 */
	var toggleFields = function(show, animate) {
		var $fields = $('fieldset#sitegear-fieldset-delivery-address div.field');
		if (animate) {
			$fields.slideToggle(show);
		} else {
			$fields.toggle(show);
		}
		$fields.find('input, select').attr('disabled', !show);
	};

	/**
	 * Setup the "delivery address is different" checkbox to show/hide the delivery address fields.  Toggle fields
	 * immediately without animation, and setup a change event to toggle with animation when the checkbox is changed.
	 */
	$(function() {
		toggleFields($('input#delivery-address-different-value-yes').change(function() {
			toggleFields($(this).is(':checked'), true);
		}).is(':checked'), false);
	});
}(jQuery));
