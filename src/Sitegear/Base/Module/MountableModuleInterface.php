<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Module;

/**
 * Defines the behaviour of modules that can be mounted to a particular root URL, and provide routing and navigation
 * data for the descendants of that root URL.
 *
 * For example, consider a product catalogue module, it might be mounted at the root URL "shop", and expose category
 * pages in the level below like "shop/widgets/" and product details pages at the level below that, with URLs like
 * "shop/widgets/red-widget".
 */
interface MountableModuleInterface {

	//-- Constants --------------------

	/**
	 * Navigation data mode used to retrieve only the "main" navigation data, that is, the navigation that should be
	 * displayed in the site's primary navigation mechanism ("main menu").
	 */
	const NAVIGATION_DATA_MODE_MAIN = 'main';

	/**
	 * Navigation data mode used to retrieve the fully expanded navigation data, which is used to generate breadcrumbs,
	 * build site maps, etc.  This is intended to represent every valid, canonical page URL within the site.
	 */
	const NAVIGATION_DATA_MODE_EXPANDED = 'expanded';

	//-- Public Methods --------------------

	/**
	 * Set the root URL assigned to this module.
	 *
	 * @param string $mountedUrl The root URL to assign to this module, relative to the site root URL.
	 *
	 * @throws \LogicException If the module is already mounted.
	 */
	public function mount($mountedUrl=null);

	/**
	 * Remove the root URL assigned to this module.
	 *
	 * @throws \LogicException If the module is not currently mounted.
	 */
	public function unmount();

	/**
	 * Retrieve the root URL assigned to this module for handling.
	 *
	 * @return string URL this module is mounted on, relative to the site root URL, or null if the module is not
	 *   currently mounted.
	 */
	public function getMountedUrl();

	/**
	 * Get the Routes for the routing component.
	 *
	 * @return \Symfony\Component\Routing\RouteCollection Route collection for this module's URLs.
	 */
	public function getRoutes();

	/**
	 * Retrieve the navigation data from this module.  That is, a hierarchical structure of all the URLs handled by
	 * this module.
	 *
	 * Note that the hierarchical structure returned does not have to correspond to the hierarchical structure of the
	 * URLs themselves, although that is a useful default configuration.
	 *
	 * @param integer $mode One of the NAVIGATION_DATA_MODE_* constants.
	 *
	 * @return null|array Hierarchical array structure, where each array node has the following keys: 'url', which
	 *   gives the full (relative) URL of the page; 'label', which gives a text label to use in generating a navigation
	 *   view; and 'children', which is an array of nested nodes (each of which may, in turn, possess a 'children'
	 *   element).  If null, this indicates that the mount point should not be represented at all in the navigation
	 *   data.
	 */
	public function getNavigationData($mode);

}
