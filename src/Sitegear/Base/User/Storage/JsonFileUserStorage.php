<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\User\Storage;

use Sitegear\Util\JsonFormatter;
use Sitegear\Util\LoggerRegistry;

/**
 * Storage implementation backed by a single JSON file.
 */
class JsonFileUserStorage implements UserStorageInterface {

	//-- Attributes --------------------

	/**
	 * @var string
	 */
	private $filename;

	/**
	 * @var boolean
	 */
	private $format;

	/**
	 * @var array[]
	 */
	private $users;

	//-- Constructor --------------------

	/**
	 * @param string $filename Absolute path of the JSON file to load the user data from.
	 * @param boolean $format Whether to use a JSON formatter to format the result.  If false, the raw result of
	 *   json_encode() will be stored in the file (one long line).
	 */
	public function __construct($filename, $format=true) {
		$this->filename = $filename;
		$this->format = $format;
		$this->users = is_file($filename) ? json_decode(file_get_contents($filename), true) : array();
	}

	//-- UserStorageInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function hasUser($id) {
		return isset($this->users[$id]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function createUser(array $data) {
		LoggerRegistry::debug('JsonFileUserStorage creating user');
		$id = sizeof($this->users);
		array_push($this->users, array(
			'data' => $data,
			'privileges' => array(),
			'active' => true
		));
		$this->store();
		return $id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function deleteUser($id) {
		LoggerRegistry::debug('JsonFileUserStorage deleting user');
		// Don't actually remove the user, just overwrite it with a placeholder.  Otherwise the id values change.
		$this->users[$id]['active'] = false;
		$this->users[$id]['data-before-deletion'] = $this->users[$id]['data'];
		$this->users[$id]['data'] = null;
		$this->users[$id]['privileges'] = null;
		$this->store();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getData($id) {
		return $this->users[$id]['data'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function setData($id, array $data) {
		$this->users[$id]['data'] = $data;
		$this->store();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPrivileges($id) {
		return $this->users[$id]['privileges'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPrivileges($id, array $privileges) {
		$this->users[$id]['privileges'] = $privileges;
		$this->store();
	}

	/**
	 * {@inheritDoc}
	 */
	public function grantPrivilege($id, $privilege) {
		$this->users[$id]['privileges'][] = $privilege;
		$this->store();
	}

	/**
	 * {@inheritDoc}
	 */
	public function revokePrivilege($id, $privilege) {
		$this->users[$id]['privileges'] = array_filter($this->getPrivileges($id), function($item) use ($privilege) {
			return ($item !== $privilege);
		});
		$this->store();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findOneUser($field, $value) {
		LoggerRegistry::info('JsonFileUserStorage looking for one user with ' . $field . ' = ' . $value);
		LoggerRegistry::info('data = ' . print_r($this->users, true));
		$result = null;
		foreach ($this->users as $id => $user) {
			if (isset($user['data'][$field]) && $user['data'][$field] === $value) {
				$result = $id;
			}
		}
		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findUsers($field, $value) {
		$result = array();
		foreach ($this->users as $id => $user) {
			if (isset($user[$field]) && $user[$field] === $value) {
				$result[] = $id;
			}
		}
		return $result;
	}

	//-- Internal Methods --------------------

	/**
	 * Do the internal work of storing the modified data.
	 */
	private function store() {
		LoggerRegistry::debug('JsonFileUserStorage saving modified user database');
		if ($this->format) {
			$formatter = new JsonFormatter();
			$result = $formatter->formatJson($this->users);
		} else {
			$result = json_encode($this->users);
		}
		file_put_contents($this->filename, $result);
	}

}
