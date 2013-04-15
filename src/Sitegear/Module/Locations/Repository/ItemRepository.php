<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Locations\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * Custom repository for the `Item` entity.
 */
class ItemRepository extends EntityRepository {

	/**
	 * Ratio to use in calculation when radius is measured in km.
	 */
	const RATIO_KM = 1.853159616;

	/**
	 * Find the locations within the given radius from the specified origin coordinates.  Calculations are done at sea
	 * level using a mean Earth radius and does not cater for altitude or "oblate spheroid" related anomalies.
	 *
	 * <strong>This method uses native SQL</strong> however it is very restricted to highly compatible functions:
	 * `SIN()`, `COS()`, `ACOS()`, and `PI()`, which should be supported on most (all?) platforms.
	 *
	 * @param array $origin An array consisting of 'latitude' and 'longitude' keys.  Other keys are ignored.
	 * @param int $radius Number of metres around the origin to include.
	 *
	 * @return array
	 */
	public function findInRadius(array $origin, $radius) {
		$resultSetMappingBuilder = new ResultSetMappingBuilder($this->getEntityManager());
		$resultSetMappingBuilder->addRootEntityFromClassMetadata('Locations:Item', 'li');
		return $this->getEntityManager()->createNativeQuery(
			sprintf(
				'select li.* from %s li where (
					ACOS(
						SIN(%s * PI() / 180) *
						SIN(li.latitude * PI() / 180) +
						COS(%s * PI() / 180) *
						COS(li.latitude * PI() / 180) *
						COS((%s - li.longitude) * PI() / 180)
					) * 180 / PI() * 60 * %s
				) <= %s',
				$this->getClassMetadata()->getTableName(),
				$origin['latitude'],
				$origin['latitude'],
				$origin['longitude'],
				self::RATIO_KM,
				$radius
			),
			$resultSetMappingBuilder
		)->execute();
	}

}
