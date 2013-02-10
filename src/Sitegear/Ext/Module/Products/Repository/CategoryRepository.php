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
class CategoryRepository extends EntityRepository {

	/**
	 * @return \Sitegear\Ext\Module\Products\Model\Category[]
	 */
	public function getRootCategories() {
		return $this->_em->createQueryBuilder()
				->select('pc')
				->from('Products:Category', 'pc')
				->where('pc.parent is null')
				->getQuery()->getResult();
	}

	/**
	 * @param int|\Sitegear\Ext\Module\Products\Model\Category $parent
	 *
	 * @return \Sitegear\Ext\Module\Products\Model\Category[]
	 */
	public function getChildCategories($parent) {
		return $this->_em->createQueryBuilder()
				->select('pc')
				->from('Products:Category', 'pc')
				->where('pc.parent = :parent')
				->setParameter('parent', $parent)
				->getQuery()->getResult();
	}

	/**
	 * @param string $urlPath
	 *
	 * @return \Sitegear\Ext\Module\Products\Model\Category
	 */
	public function selectCategoryByUrlPath($urlPath) {
		return $this->_em->createQueryBuilder()
				->select('pc')
				->from('Products:Category', 'pc')
				->where('pc.urlPath = :urlPath')
				->setParameter('urlPath', $urlPath)
				->getQuery()->getSingleResult();
	}

}
