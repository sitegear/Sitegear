<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Default settings for Sitegear Products Module.
 */
return array(

	/**
	 * Directory in this module's path at the site level where the product item content is stored as files.
	 */
	'item-path' => 'items',

	/**
	 * Directory in this module's path at the site level where the product category content is stored as files.
	 */
	'category-path' => 'categories',

	/**
	 * Title displayed on the products index page, and on the product category and item pages after the name.
	 */
	'title' => 'Products',

	/**
	 * Route settings.
	 */
	'routes' => array(

		/**
		 * URL path element under the mounted root URL, containing all product category landing pages.
		 */
		'category' => 'category',

		/**
		 * URL path element under the mounted root URL, containing all product item details pages.
		 */
		'item' => 'item'

	),

	/**
	 * Settings for navigation data generation.
	 */
	'navigation' => array(

		/**
		 * Maximum number of levels to show in navigation; 0 to show all category levels.
		 */
		'max-depth' => 1,

		/**
		 * Tooltip format mask, the single placeholder is given the name of the category.
		 */
		'tooltip' => 'Find out about our range of %s'

		// TODO
//		'all-products-link' => false
//		'all-categories-link' => false

	),

	/**
	 * Page specific settings.
	 */
	'page' => array(

		/**
		 * Products main landing page settings.
		 */
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

		/**
		 * Category landing page settings.
		 */
		'category' => array(

			/**
			 * Number of characters to show in each item's preview.
			 */
			'excerpt-length' => 100,

			/**
			 * Text to use for "read more" links, null to disable completely (only have heading links).
			 */
			'read-more' => 'Read More &raquo;'

		),

		/**
		 * Product detail page settings.
		 */
		'item' => array(

			/**
			 * Whether or not to display the "Add to Trolley" form.  This requires that the Customer module is
			 * correctly configured.
			 */
			'display-add-to-trolley-form' => false

		)
	)
);
