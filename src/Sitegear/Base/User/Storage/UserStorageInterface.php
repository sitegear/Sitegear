<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\User\Storage;

/**
 * Defines the behaviour of a backend storage object for user information.
 */
interface UserStorageInterface {

	/**
	 * Create a new user with the given email address and initial data.
	 *
	 * @param string $email
	 * @param array $data
	 */
	public function createUser($email, array $data);

	/**
	 * Delete the specified user completely.
	 *
	 * @param string $email
	 */
	public function deleteUser($email);

	/**
	 * Determine whether the given user email address exists.
	 *
	 * @param string $email
	 *
	 * @return boolean
	 */
	public function hasUser($email);

	/**
	 * Get the data set for the given email address.
	 *
	 * @param string $email
	 *
	 * @return array
	 */
	public function getData($email);

	/**
	 * Completely replace the given user's data set.  This removes all existing data and replaces it with the given
	 * replacement data array.
	 *
	 * @param string $email
	 * @param array $data
	 */
	public function setData($email, array $data);

	/**
	 * Get the list of privileges assigned to the user with the given email address.
	 *
	 * @param string $email
	 *
	 * @return string[]
	 */
	public function getPrivileges($email);

	/**
	 * Completely replace the privileges assigned to the user with the given email address.
	 *
	 * @param string $email
	 * @param string[] $privileges
	 */
	public function setPrivileges($email, array $privileges);

	/**
	 * Grant the given privilege to the specified user.
	 *
	 * @param string $email
	 * @param string $privilege
	 */
	public function grantPrivilege($email, $privilege);

	/**
	 * Revoke the given privilege from the specific user.
	 *
	 * @param string $email
	 * @param string $privilege
	 */
	public function revokePrivilege($email, $privilege);

}
