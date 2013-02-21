<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Default settings for Sitegear News Module.
 */
return array(

	/**
	 * Directory in this module's path at the site level where the news item content is stored as files.
	 */
	'item-path' => 'items',

	/**
	 * Title displayed on the news index page, and on the news item page after the item headline.
	 */
	'title' => 'News',

	/**
	 * Main page heading.
	 */
	'heading' => 'News',

	/**
	 * Settings for navigation data generation.
	 */
	'navigation' => array(

		/**
		 * Maximum number of news items to include in navigation.
		 */
		'item-limit' => 5,

		/**
		 * Text to use on an additional link at the end of the list, which points to "all news", i.e. an additional
		 * link to the top-level news index page, but with "?more=1".  If false or empty string, no link is added.
		 */
		'all-news-link' => false

	),

	/**
	 * Page specific settings.
	 */
	'page' => array(

		/**
		 * Settings for the index page, which lists the latest or all news items.
		 */
		'index' => array(

			/**
			 * Text for the "show more news items" button.
			 */
			'show-more' => 'Show more items',

			/**
			 * Text for the "show less news items" button.
			 */
			'show-less' => 'Show less items',

			/**
			 * How to show dates, null to hide completely.
			 */
			'date-format' => 'Y-m-d',

			/**
			 * Format for published date.  Tokens:
			 *
			 * %publishedDate% -- The date formatted according to 'date-format' configuration setting.
			 */
			'published' => 'Published: %publishedDate%',

			/**
			 * Number of news items to show.
			 */
			'item-limit' => 5,

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
		 * Settings for the news item view page.
		 */
		'item' => array(

			/**
			 * How to show dates, null to hide completely.
			 *
			 * TODO Centralise this
			 */
			'date-format' => 'Y-m-d',

			/**
			 * Format for published date.  Tokens:
			 *
			 * %publishedDate% -- the date formatted according to 'date-format' configuration setting.
			 */
			'published' => 'Published: %publishedDate%',

			/**
			 * Format for the "back to index" link.  Tokens:
			 *
			 * %rootUrl% -- the URL of the top-level news landing page.
			 */
			'back-link' => 'Back to <a href="%rootUrl%">news index</a>.'

		)
	),

	/**
	 * Component specific settings.
	 */
	'component' => array(

		/**
		 * Settings for the latest headlines component.
		 */
		'latest-headlines' => array(

			/**
			 * How to show dates, null to hide completely.
			 *
			 * TODO Centralise this
			 */
			'date-format' => null,

			/**
			 * Format for published date.  Tokens:
			 *
			 * %publishedDate% -- the date formatted according to 'date-format' configuration setting.
			 */
			'published' => '%publishedDate%',

			/**
			 * Number of news items to show.
			 */
			'item-limit' => 3,

			/**
			 * Number of characters to show in each item's preview.
			 */
			'excerpt-length' => 100,

			/**
			 * Text to use for "read more" links, null to disable completely (only have heading links).
			 */
			'read-more' => 'Read More &raquo;'

		)
	)

);
