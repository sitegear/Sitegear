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
	 * Directory settings for description data files.
	 */
	'paths' => array(

		/**
		 * Directory in this module's path at the site level where the location region content is stored as files.
		 */
		'region' => 'regions',

		/**
		 * Directory in this module's path at the site level where the location item content is stored as files.
		 */
		'item' => 'items'
	),

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
	 * Settings that are used in multiple places and should have the same value everywhere.
	 */
	'common' => array(

		/**
		 * Title displayed on the locations index page, and on the region and location pages after the name.
		 */
		'title' => 'Locations',

		/**
		 * Main page heading.
		 */
		'heading' => 'Locations',

		/**
		 * Text to use for "read more" links, null to disable completely (only have heading links).
		 */
		'read-more' => 'Read More &raquo;',

		/**
		 * Text to use in place of a region description when the region description is not found.
		 */
		'missing-region-description' => 'Region description not found.'

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
			 * Component to use when no regions are found.
			 */
			'no-regions' => 'no-regions',

			/**
			 * Where to show the search form, either 'before' or 'after' or false to not show it at all.
			 */
			'show-search' => 'before'

		),

		'region' => array(

			/**
			 * Number of characters to show in each item's preview.
			 */
			'excerpt-length' => 100,

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
			 * Additional title text for the search page.
			 */
			'search-title' => 'Search',

			/**
			 * Additional heading text for the search page.
			 */
			'search-heading' => 'Search',

			/**
			 * Format specifier for the results description (page intro text).
			 */
			'results-description-format' => '<p>You searched for locations within %radius%km of &quot;%query%&quot;</p>'

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
