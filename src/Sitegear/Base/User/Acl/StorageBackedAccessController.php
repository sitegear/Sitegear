<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\User\Acl;

use Sitegear\Base\User\Storage\UserStorageInterface;

/**
 * Simple default implementation of AuthenticatorInterface tied to a UserStorageInterface implementation.
 */
class StorageBackedAccessController implements AccessControllerInterface {

	//-- Attributes --------------------

	private $storage;

	//-- Constructor --------------------

	public function __construct(UserStorageInterface $storage) {
		$this->storage = $storage;
	}

	//-- AccessControllerInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function checkPrivilege($id, $privilege) {
		return in_array($privilege, $this->storage->getPrivileges($id));
	}

}
