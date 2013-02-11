<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Locations\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Custom repository for the `Item` entity.
 */
class ItemRepository extends EntityRepository {

	/**
	 * Find the locations within the given radius from the specified origin coordinates.  Calculations are done at sea
	 * level using a mean Earth radius and does not cater for altitude or "oblate spheroid" related anomalies.
	 *
	 * @param array $origin An array consisting of 'latitude' and 'longitude' keys.  Other keys are ignored.
	 * @param int $radius Number of metres around the origin to include.
	 */
	public function findInRadius(array $origin, $radius) {
//		$select = sprintf(
//		      '((ACOS(SIN(%s * PI() / 180) * SIN(li.latitude * PI() / 180) + COS(%s * PI() / 180) * COS(li.latitude * PI() / 180) * COS((%s - li.longitude) * PI() / 180)) * 180 / PI()) * 60 * 1.853159616) AS distance',
//		      $latitude, $latitude, $longitude);
//
	}

}
