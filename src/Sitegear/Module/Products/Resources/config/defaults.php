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
	 * Directory settings for description data files.
	 */
	'paths' => array(

		/**
		 * Directory in this module's path at the site level where the product item content is stored as files.
		 */
		'item' => 'items',

		/**
		 * Directory in this module's path at the site level where the product category content is stored as files.
		 */
		'category' => 'categories'
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
		 * Tooltip format mask.  Tokens:
		 *
		 * %categoryName% -- the name of the category.
		 */
		'tooltip' => 'Find out about our range of %categoryName%'

		// TODO
//		'all-products-link' => false
//		'all-categories-link' => false

	),

	/**
	 * Settings that are used in multiple places and should have the same value everywhere.
	 */
	'common' => array(

		/**
		 * Title displayed on the products index page, and on the product category and item pages after the name.
		 */
		'title' => 'Products',

		/**
		 * Main page heading.
		 */
		'heading' => 'Products'
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
			'display-add-trolley-item-form' => false

		)
	)
);
