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
 * Custom entity repository for the Locations module.
 */
class ItemRepository extends EntityRepository {

	/**
	 * Retrieve the total number of headlines currently available.
	 *
	 * @return integer
	 */
	public function getItemCount() {
		return $this->_em->createQueryBuilder()
				->select('count(li)')
				->from('Locations:Item', 'li')
				->getQuery()
				->getSingleScalarResult();
	}

}
