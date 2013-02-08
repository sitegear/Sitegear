<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\User\Auth;

use Sitegear\Util\LoggerRegistry;

class EmailPasswordAuthenticator extends AbstractStorageBackedAuthenticator {

	/**
	 * {@inheritDoc}
	 */
	public function checkCredentials(array $credentials) {
		LoggerRegistry::debug('EmailPasswordAuthenticator checking credentials');
		if (!isset($credentials['email']) || !isset($credentials['password'])) {
			throw new \InvalidArgumentException('EmailPasswordAuthenticator expects "email" and "password" credential keys, insufficient credentials supplied.');
		}
		$result = null;
		$id = $this->getStorage()->findOneUser('email', $credentials['email']);
		if (!is_null($id)) {
			$data = $this->getStorage()->getData($id);
			// TODO Not this
			if ($data['password'] === $credentials['password']) {
				$result = $id;
			}
		}
		return $result;
	}

}
