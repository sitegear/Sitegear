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

	'search' => array(

		/**
		 * An sprintf() mask which allows addition of boilerplate to provided queries.  The single placeholder is
		 * replaced by the query entered by the user.  For example "%s, Australia" will explicitly add ", Australia"
		 * to the end of every query.
		 */
		'query-mask' => '%s'

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
	),

	/**
	 * Component specific settings.
	 */
	'component' => array(

		'search-form' => array(

			/**
			 * Maximum number of results to show on the results page.
			 */
			'result-limit' => 10,

			/**
			 * List of options for the radius selector.
			 */
			'radius-options' => array(
				array(
					'value' => 1000,
					'label' => '1km',
					'zoom' => 15
				),
				array(
					'value' => 5000,
					'label' => '5km',
					'zoom' => 12,
					'default' => true
				),
				array(
					'value' => 10000,
					'label' => '10km',
					'zoom' => 11
				),
				array(
					'value' => 25000,
					'label' => '25km',
					'zoom' => 10
				),
				array(
					'value' => 100000,
					'label' => '100km',
					'zoom' => 9
				),
				array(
					'value' => 250000,
					'label' => '250km',
					'zoom' => 8
				)
			)
		)
	)

);
