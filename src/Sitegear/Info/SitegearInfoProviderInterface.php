<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Info;

/**
 * Defines the behaviour of an object that can provide information about the Sitegear framework.
 */
interface SitegearInfoProviderInterface {

	/**
	 * @return string Sitegear application name, for display purposes.
	 */
	public function getSitegearDisplayName();

	/**
	 * @return string Version string.
	 */
	public function getSitegearVersion();

	/**
	 * @return string Description.
	 */
	public function getSitegearDescription();

	/**
	 * @return string License name(s).
	 */
	public function getSitegearLicense();

	/**
	 * @return string Home page URL.
	 */
	public function getSitegearHomepage();

	/**
	 * @return array Array of authors.
	 */
	public function getSitegearAuthors();

	/**
	 * @return string Short version identifier, for use in HTTP headers etc.
	 */
	public function getSitegearVersionIdentifier();

	/**
	 * Get the root file path of the Sitegear application framework source code (the "src" directory).
	 *
	 * @return string
	 */
	public function getSitegearRoot();

	/**
	 * Get the root file path of the Sitegear Vendor Resources package.
	 *
	 * @return string
	 */
	public function getSitegearVendorResourcesRoot();

}
