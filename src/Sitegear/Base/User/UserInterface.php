<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\User;

use Sitegear\Base\User\Auth\AuthenticatorInterface;

/**
 * Represents the currently logged in user.
 */
interface UserInterface {

	/**
	 * Get the user id of the currently logged in user
	 *
	 * @return integer User id of the currently logged in user, or null if no user is logged in (guest).
	 */
	public function getUserId();

	/**
	 * Get the user's email address.
	 *
	 * @return string Email address of the user.
	 */
	public function getEmail();

	/**
	 * Get the name of the user for display purposes.
	 *
	 * @return string Name of the user.
	 */
	public function getName();

	/**
	 * Get named data for the currently logged in user.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getData($key);

}
