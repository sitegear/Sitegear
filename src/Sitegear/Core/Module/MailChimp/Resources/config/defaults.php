<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Default settings for Sitegear MailChimp integration module.
 */
return array(

	'api' => array(

		/**
		 * The API key can either be specified as an argument to individual methods, or this configuration key can be
		 * overridden and this will be used as the default API key for all MCAPI calls.
		 */
		'key' => null,

		/**
		 * This can be used to set fixed values for specific merge fields in ALL MailChimp API calls, by providing a
		 * map where the keys are merge field names and the values are the fixed override values.  This can be used to
		 * add values that are not visible in the HTML or URL parameters, and also to provide hardcoded overrides for
		 * the SUBURL merge field, which MailChimp rejects if it is not a contactable URL (e.g. localhost is rejected).
		 */
		'merge-field-overrides' => array(),

		/**
		 * Settings that are applied by default to the API calls.  These can be also overridden on a case-by-case basis
		 * using method parameters.
		 */
		'defaults' => array(

			/**
			 * Defaults for the listSubscribe() API call.
			 */
			'list-subscribe' => array(
				'email-type' => 'html',
				'double-optin' => true,
				'update-existing' => true,
				'replace-interests' => true,
				'send-welcome' => true
			)
		)
	)

);
