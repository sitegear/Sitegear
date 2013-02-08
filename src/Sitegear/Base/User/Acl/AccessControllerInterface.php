<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\User\Acl;

/**
 * Defines the behaviour of an object which performs access control related operations, i.e. checking and managing
 * privileges applied to users.
 *
 * The Sitegear access control system is kept intentionally very simple.  Each privilege represents access to a subset
 * of data.  Privileges are simply strings; the exact designations are module-dependant.  Alternative methods of
 * approving access to particular data, e.g. ownership, can be implemented on a per-module basis.
 */
interface AccessControllerInterface {

	/**
	 * Determine whether the given user has the specified privilege.
	 *
	 * @param integer $id
	 * @param string $privilege
	 *
	 * @return boolean
	 */
	public function checkPrivilege($id, $privilege);

}
