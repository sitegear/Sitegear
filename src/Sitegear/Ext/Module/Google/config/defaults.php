<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Default settings for Sitegear Google integration module.
 */
return array(

	/**
	 * Configuration for Google Analytics integration.
	 */
	'analytics' => array(

		/**
		 * Whether Analytics is enabled; this is good to override in development environments.
		 */
		'enabled' => true,

		/**
		 * API configuration.  Key is normally provided as a view parameter, but may also be defined in configuration.
		 */
		'api' => array(
			'key' => null,
			'additional-calls' => array()
		)
	),

	/**
	 * Configuration for Google Maps integration.
	 */
	'maps' => array(

		/**
		 * API configuration.  Key is normally provided as a view parameter, but may also be defined in configuration.
		 */
		'api' => array(
			'host' => 'maps.google.com.au',
			'path' => '/maps/api/js',
			'key' => null,
		)
	),

	/**
	 * Resources
	 */
	'resources' => array(

		/**
		 * Google Maps Sitegear integration script.
		 */
		'script:google:maps' => array(
			'type' => 'script',
			'url' => 'sitegear/resources/google/sitegear.google-maps.js',
			'requires' => array(
				'script:vendor:jquery',
				'script:sitegear:utilities'
			)
		)
	)

);
