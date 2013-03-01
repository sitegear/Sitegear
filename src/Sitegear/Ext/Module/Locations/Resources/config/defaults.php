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
	 * Main page heading.
	 */
	'heading' => 'Locations',

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
		'item' => 'item',

		/**
		 * URL path element under the mounted root URL, containing the search page.
		 */
		'search' => 'search'

	),

	/**
	 * Settings for navigation data generation.
	 */
	'navigation' => array(

		/**
		 * Maximum number of levels to show in navigation; 0 to show all region levels.
		 */
		'max-depth' => 1,

		/**
		 * Format to use for navigation item tooltips.  Tokens:
		 *
		 * %regionName% -- The name of the region.
		 */
		'tooltip-format' => 'Find locations in "%regionName%"'

	),

	'search' => array(

		/**
		 * An sprintf() mask which allows addition of boilerplate to provided queries.  Tokens:
		 *
		 * %query% -- the original query as entered
		 */
		'query-mask' => '%query%'

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
			'read-more' => 'Read More &raquo;',

			/**
			 * Component to use when no regions are found.
			 */
			'no-regions' => 'no-regions'

		),

		'region' => array(

			/**
			 * Number of characters to show in each item's preview.
			 */
			'excerpt-length' => 100,

			/**
			 * Text to use for "read more" links, null to disable completely (only have heading links).
			 */
			'read-more' => 'Read More &raquo;',

			/**
			 * Component to use when no child regions are found.
			 */
			'no-regions' => 'no-sub-regions'

		),

		'item' => array(

		),

		'search' => array(

			/**
			 * Number of characters to show in each item's preview.
			 */
			'excerpt-length' => 100,

			/**
			 * Text to use for "read more" links, null to disable completely (only have heading links).
			 */
			'read-more' => 'Read More &raquo;',

			/**
			 * Additional title text for the search page.
			 */
			'search-title' => 'Search'

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
					'value' => 1,
					'label' => '1km',
					'zoom' => 15
				),
				array(
					'value' => 5,
					'label' => '5km',
					'zoom' => 12,
					'default' => true
				),
				array(
					'value' => 10,
					'label' => '10km',
					'zoom' => 11
				),
				array(
					'value' => 25,
					'label' => '25km',
					'zoom' => 10
				),
				array(
					'value' => 100,
					'label' => '100km',
					'zoom' => 9
				),
				array(
					'value' => 250,
					'label' => '250km',
					'zoom' => 8
				)
			)
		)
	),

	/**
	 * Settings for the location search form.
	 */
	'location-search-form' => array(

		/**
		 * Form key for the search form.
		 */
		'key' => 'location-search',

		/**
		 * Filename of the search form, relative to the module root at either the site-specific or built-in level.
		 */
		'filename' => 'location-search-form.json'

	)

);
