<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Products\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Custom entity repository for the Products module.
 */
class ItemRepository extends EntityRepository {

	/**
	 * @param int|\Sitegear\Ext\Module\Products\Model\Category $category
	 *
	 * @return \Sitegear\Ext\Module\Products\Model\Item[]
	 */
	public function getActiveItemsInCategory($category) {
		return $this->_em->createQueryBuilder()
				->select('pi')
				->from('Products:Item', 'pi')
				->join('pi.categoryAssignments', 'pca')
				->where('pca.category = :category')
				->andWhere('pi.active = true')
				->setParameter('category', $category)
				->getQuery()->getResult();
	}

}
