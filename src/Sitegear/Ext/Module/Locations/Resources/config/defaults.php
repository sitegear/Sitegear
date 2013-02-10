<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Default settings for Sitegear Locations module.
 */
return array(

	/**
	 * Directory in this module's path at the site level where the location content is stored as files.
	 */
	'item-path' => 'items',

	/**
	 * Directory in this module's path at the site level where the region content is stored as files.
	 */
	'region-path' => 'regions',

	/**
	 * Title displayed on the locations index page, and on the region and location pages after the name.
	 */
	'title' => 'Locations',

	/**
	 * Route settings.
	 */
	'routes' => array(

		/**
		 * URL path element under the mounted root URL, containing all region landing pages.
		 */
		'region' => 'region',

		/**
		 * URL path element under the mounted root URL, containing all location details pages.
		 */
		'item' => 'item'

	),

	/**
	 * Settings for navigation data generation.
	 */
	'navigation' => array(

		/**
		 * Maximum number of levels to show in navigation; 0 to show all region levels.
		 */
		'max-depth' => 1

	),

	/**
	 * Page specific settings.
	 */
	'page' => array(

		'index' => array(

			/**
			 * Number of characters to show in each item's preview.
			 */
			'excerpt-length' => 100,

			/**
			 * Text to use for "read more" links, null to disable completely (only have heading links).
			 */
			'read-more' => 'Read More &raquo;'

		),

		'region' => array(

			/**
			 * Number of characters to show in each item's preview.
			 */
			'excerpt-length' => 100,

			/**
			 * Text to use for "read more" links, null to disable completely (only have heading links).
			 */
			'read-more' => 'Read More &raquo;'

		),

		'item' => array(

		)

	)
);
