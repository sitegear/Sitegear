<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Default settings for Sitegear Navigation Module.
 */
return array(

	/**
	 * Component-specific settings.
	 */
	'components' => array(

		/**
		 * Navigation component settings.
		 */
		'navigation' => array(

			/**
			 * Additional attributes for list elements.
			 */
			'list-attributes' => array(),

			/**
			 * Additional attributes for list item elements.
			 */
			'item-attributes' => array(),

			/**
			 * Format for link text.  Tokens:
			 *
			 * %label% -- the label from the navigation data.
			 */
			'link-format' => '%label%',

			/**
			 * Whether or not to display tooltips (title attributes) on navigation items.
			 */
			'display-tooltips' => true,

			/**
			 * Classes to apply to specially marked navigation items.
			 */
			'classes' => array(

				/**
				 * Class to apply to current page or ancestor of current page.
				 */
				'current' => 'current',

				/**
				 * Class to apply to current page (exact URL match)
				 */
				'current-page' => 'current-page',

				/**
				 * Class suffix to use for an index page (default page within a URL path)
				 */
				'index' => 'index',

				/**
				 * Class to apply to all list elements, and base name for all list classes
				 */
				'list' => 'navigation',

				/**
				 * Class to apply to all list item elements, and base name for all list item classes
				 */
				'item' => 'navigation-item',

				/**
				 * Class to apply to all heading elements (non-link items), and base name for all heading classes.
				 */
				'heading' => 'navigation-heading',

				/**
				 * Class to apply to all link item elements, and base name for all link classes.
				 */
				'link' => 'navigation-link'

			)

		),

		/**
		 * Breadcrumbs component settings.
		 */
		'breadcrumbs' => array(

			/**
			 * Separator to use between breadcrumb elements.
			 */
			'separator' => ' &gt; ',

			/**
			 * Whether or not to show the breadcrumbs on the homepage.  (This is good for design consistency, but can
			 * be redundant, and the home page is often graphically different anyway so the missing breadcrumb is
			 * okay).
			 */
			'show-on-homepage' => false,

			/**
			 * Whether or not to automatically append a breadcrumb element for the home page, before the "natural"
			 * breadcrumb determined from the navigation, with the exception of the homepage itself.  This allows a
			 * navigation structure with the home page link at the same level as the other top-level links, but still
			 * provides a sensible default configuration for breadcrumbs based on the navigation data structure.
			 */
			'prepend-homepage' => true,

			/**
			 * Whether and how to represent the current page, i.e. the last element of the breadcrumb.  Should be
			 * either 'link' to display a link to the current page as the last element, 'label' to display a label only
			 * as the last element (the default), empty string ('', or in fact, any other string) to display no text at
			 * all, but to display the final breadcrumb separator (this allows "run-on" to a subsequent heading
			 * element), or boolean false to omit both the current page and the final separator.
			 */
			'current-page-style' => 'label',

			/**
			 * Classes to apply to specially marked navigation items.
			 */
			'classes' => array(

				/**
				 * Class to apply to the breadcrumb link elements.
				 */
				'link' => 'breadcrumb-link',

				/**
				 * Class to apply to the breadcrumb link or span representing the current page.
				 */
				'current-page' => 'current-page'

			),

			/**
			 * Format masks for sprintf() when generating breadcrumb items.
			 */
			'formats' => array(

				/**
				 * Format mask for link elements.  The placeholders are the link URL, classname(s) and label text.
				 * Tokens:
				 *
				 * %url% -- the URL of the link
				 * %class% -- CSS class names as determined programmatically
				 * %text% -- text for the link
				 */
				'link' => '<a href="%url%" class="%class%">%text%</a>',

				/**
				 * Format mask for non-link elements.  The placeholders are the classname(s) and label text.  Tokens:
				 *
				 * %class% -- CSS class names as determined programmatically
				 * %text% -- text for the label
				 */
				'label' => '<span class="%class%">%text%</span>'
			)
		)
	)
);
