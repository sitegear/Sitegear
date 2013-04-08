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
	 * @inheritdoc
	 */
	public function hasUser($email) {
		return isset($this->users[$email]);
	}

	/**
	 * @inheritdoc
	 */
	public function createUser($email, array $data) {
		LoggerRegistry::debug('JsonFileUserStorage creating user');
		if (array_key_exists($email, $this->users)) {
			throw new \InvalidArgumentException(sprintf('JsonFileUserStorage cannot create user with email address "%s", that email address is already registered.', $email));
		}
		$this->users[$email] = array(
			'data' => $data,
			'privileges' => array(),
			'active' => true
		);
		$this->store();
	}

	/**
	 * @inheritdoc
	 */
	public function deleteUser($email) {
		LoggerRegistry::debug('JsonFileUserStorage deleting user');
		if (!array_key_exists($email, $this->users)) {
			throw new \InvalidArgumentException(sprintf('JsonFileUserStorage cannot delete user with email address "%s", that email address is not registered.', $email));
		}
		unset($this->users[$email]);
		$this->store();
	}

	/**
	 * @inheritdoc
	 */
	public function getData($email) {
		return $this->users[$email]['data'];
	}

	/**
	 * @inheritdoc
	 */
	public function setData($email, array $data) {
		$this->users[$email]['data'] = $data;
		$this->store();
	}

	/**
	 * @inheritdoc
	 */
	public function getPrivileges($email) {
		return $this->users[$email]['privileges'];
	}

	/**
	 * @inheritdoc
	 */
	public function setPrivileges($email, array $privileges) {
		$this->users[$email]['privileges'] = $privileges;
		$this->store();
	}

	/**
	 * @inheritdoc
	 */
	public function grantPrivilege($email, $privilege) {
		$this->users[$email]['privileges'][] = $privilege;
		$this->store();
	}

	/**
	 * @inheritdoc
	 */
	public function revokePrivilege($email, $privilege) {
		$this->users[$email]['privileges'] = array_filter($this->getPrivileges($email), function($item) use ($privilege) {
			return ($item !== $privilege);
		});
		$this->store();
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
