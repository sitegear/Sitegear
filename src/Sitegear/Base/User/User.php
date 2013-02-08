<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\User;

use Sitegear\Base\User\Storage\UserStorageInterface;
use Sitegear\Base\User\UserInterface;
use Sitegear\Util\LoggerRegistry;

/**
 * Simple wrapper object to represent the currently logged in user based on the Session.
 */
class User implements UserInterface {

	//-- Constants --------------------

	/**
	 * Field name passed to getData() from getEmail()
	 */
	const DATA_FIELD_EMAIL = 'email';

	/**
	 * Field name passed to getData() from getName()
	 */
	const DATA_FIELD_NAME = 'name';

	//-- Attributes --------------------

	/**
	 * @var integer
	 */
	private $id;

	/**
	 * @var \Sitegear\Base\User\Storage\UserStorageInterface
	 */
	private $storage;

	//-- Constructor --------------------

	/**
	 * @param integer $id
	 * @param \Sitegear\Base\User\Storage\UserStorageInterface $storage
	 */
	public function __construct($id, UserStorageInterface $storage) {
		$this->id = $id;
		$this->storage = $storage;
	}

	//-- Public Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getUserId() {
		return $this->id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEmail() {
		return $this->getData(self::DATA_FIELD_EMAIL);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return $this->getData(self::DATA_FIELD_NAME);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getData($key) {
		$userId = $this->getUserId();
		$result = null;
		if (!is_null($userId)) {
			$data = $this->storage->getData($userId);
			if (isset($data[$key])) {
				$result = $data[$key];
			}
		}
		return $result;
	}

}
