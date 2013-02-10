<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Info;

/**
 * Defines the behaviour of an object that can provide information about the website.
 */
interface SiteInfoProviderInterface {

	/**
	 * @return string A unique ID for the site, useful for session namespace, etc.
	 */
	public function getIdentifier();

	/**
	 * @return string The name of the site, to display in content management tools and other programmatic contexts.
	 */
	public function getDisplayName();

	/**
	 * @return string The URL of the site's logo.  Relative URLs are relative to the site base.
	 */
	public function getLogoUrl();

	/**
	 * @return string The site administrator's name, for display on error pages.
	 */
	public function getAdministratorName();

	/**
	 * @return string The site administrator's email address, for display on error pages.
	 */
	public function getAdministratorEmail();

	/**
	 * @param string $key The email address name to lookup.  Usually a role like 'admin' or 'contact'.
	 *
	 * @return string The email address for the site with the given key.
	 */
	public function getSiteEmail($key);

	/**
	 * Get the site root file path, which is the parent to the various site-level resource directories.
	 *
	 * @return string Root absolute file path.
	 */
	public function getSiteRoot();

	/**
	 * Get the absolute file path to the given site resource (i.e. view script, data file, etc) within the given
	 * module, at the given location.
	 *
	 * @param string $location One of the constants defined in ResourceLocations.
	 * @param string|\Sitegear\Base\Module\ModuleInterface $module Module that contains the resource, or module short
	 *   name.
	 * @param string $resource Resource to find.
	 *
	 * @return string
	 */
	public function getSitePath($location, $module, $resource);

	/**
	 * Get the absolute file path to the given public resource (i.e. javascript, CSS, image, etc) within the given
	 * module, at the given location.
	 *
	 * @param string $location One of the constants defined in ResourceLocations.
	 * @param string|\Sitegear\Base\Module\ModuleInterface $module Module that contains the resource.
	 * @param string $resource Resource to find.
	 *
	 * @return string
	 */
	public function getPublicPath($location, $module, $resource);

}
