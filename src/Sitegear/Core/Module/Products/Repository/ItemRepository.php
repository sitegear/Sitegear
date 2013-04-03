<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\Products\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Custom entity repository for the Products module.
 */
class ItemRepository extends EntityRepository {

	/**
	 * @param int|\Sitegear\Core\Module\Products\Model\Category $category
	 *
	 * @return \Sitegear\Core\Module\Products\Model\Item[]
	 */
	public function getActiveItemsInCategory($category) {
		return $this->getEntityManager()->createQueryBuilder()
				->select('pi')
				->from('Products:Item', 'pi')
				->join('pi.categoryAssignments', 'pca')
				->where('pca.category = :category')
				->andWhere('pi.active = true')
				->setParameter('category', $category)
				->getQuery()->getResult();
	}

}
