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
	 * Settings that are used in multiple places and should have the same value everywhere.
	 */
	'common' => array(

		/**
		 * Title displayed on the news index page, and on the news item page after the item headline.
		 */
		'title' => 'News',

		/**
		 * Main page heading.
		 */
		'heading' => 'News',

		/**
		 * Text to use for "read more" links, null to disable completely (only have heading links).
		 */
		'read-more' => 'Read More &raquo;',

		/**
		 * How to show dates, null to hide completely.
		 *
		 * TODO Centralise this
		 */
		'date-format' => 'Y-m-d'

	),

	/**
	 * Settings for navigation data generation.
	 */
	'navigation' => array(

		/**
		 * Maximum number of news items to include in navigation.
		 */
		'item-limit' => 5,

		/**
		 * Settings to use for item links, in each value, the token `%headline%` is replaced by the item's headline and
		 * the token `%datePublished%` is replaced by the item's publishing date.
		 */
		'item-link' => array(
			'label' => '%headline%',
			'tooltip' => 'Read this news item "%headline%" published %datePublished%'
		),

		/**
		 * Settings to use for an additional link at the end of the list, which points to "all news", i.e. an
		 * additional link to the top-level news index page, but with "?more=1".  If `display` is false, no link is
		 * added.  No token replacements are performed on the value.
		 */
		'all-news-link' => array(
			'display' => false,
			'label' => 'News Archive',
			'tooltip' => 'View index of all news items'
		)

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
			'excerpt-length' => 100

		),

		/**
		 * Settings for the news item view page.
		 */
		'item' => array(

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
			'back-link' => 'Back to <a href="%indexUrl%">news index</a>.'

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
			'excerpt-length' => 100

		)
	)

);
