<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\News\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Custom entity repository for the News module.
 */
class ItemRepository extends EntityRepository {

	/**
	 * Retrieve the total number of headlines currently available.
	 *
	 * @return integer
	 */
	public function getItemCount() {
		return $this->_em->createQueryBuilder()
				->select('count(ni)')
				->from('News:Item', 'ni')
				->getQuery()
				->getSingleScalarResult();
	}

	/**
	 * Retrieve the latest headlines, up to the given limit.
	 *
	 * @param integer $itemLimit
	 *
	 * @return array
	 */
	public function findLatestItems($itemLimit) {
		return $this->findBy(array(), array( 'datePublished' => 'desc' ), $itemLimit > 0 ? $itemLimit : null);
	}

}
