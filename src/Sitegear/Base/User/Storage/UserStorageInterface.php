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
	 * Create a new user, which has a new unique identifier.
	 *
	 * @param array $data
	 *
	 * @return integer Identifier of the created user.
	 */
	public function createUser(array $data);

	/**
	 * Delete the specified user completely.
	 *
	 * @param integer $id
	 */
	public function deleteUser($id);

	/**
	 * Determine whether the given user id exists.
	 *
	 * @param integer $id
	 *
	 * @return boolean
	 */
	public function hasUser($id);

	/**
	 * Get the data set for the given user id.
	 *
	 * @param integer $id
	 *
	 * @return array
	 */
	public function getData($id);

	/**
	 * Completely replace the given user's data set.  This removes all existing data and replaces it with the given
	 * replacement data array.
	 *
	 * @param integer $id
	 * @param array $data
	 */
	public function setData($id, array $data);

	/**
	 * Get the list of privileges assigned to the user with the given id.
	 *
	 * @param integer $id
	 *
	 * @return string[]
	 */
	public function getPrivileges($id);

	/**
	 * Completely replace the privileges assigned to the user with the given id.
	 *
	 * @param integer $id
	 * @param string[] $privileges
	 */
	public function setPrivileges($id, array $privileges);

	/**
	 * Grant the given privilege to the specified user.
	 *
	 * @param integer $id
	 * @param string $privilege
	 */
	public function grantPrivilege($id, $privilege);

	/**
	 * Revoke the given privilege from the specific user.
	 *
	 * @param integer $id
	 * @param string $privilege
	 */
	public function revokePrivilege($id, $privilege);

	/**
	 * Retrieve a single record which has the specified value in the specified field.
	 *
	 * @param string $field Field name.
	 * @param mixed $value Value to find.
	 *
	 * @return integer|null Identifier of the single located user, or null if no user matches.
	 */
	public function findOneUser($field, $value);

	/**
	 * Retrieve any number of users which match the specified value in the specified field.
	 *
	 * @param string $field Field name.
	 * @param mixed $value Value to find.
	 *
	 * @return integer[] Array of identifiers.  If no user matches, this will be an empty array.
	 */
	public function findUsers($field, $value);

}
