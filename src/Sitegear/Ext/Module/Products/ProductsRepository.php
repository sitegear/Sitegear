<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Products;

use Doctrine\ORM\EntityRepository;

/**
 * Custom entity repository for the Products module.
 */
class ProductsRepository extends EntityRepository {

	/**
	 * @return \Sitegear\Ext\Module\Products\Entities\Category[]
	 */
	public function getRootCategories() {
		return $this->_em->createQueryBuilder()
				->select('pc')
				->from('Products:Category', 'pc')
				->where('pc.parent is null')
				->getQuery()->getResult();
	}

	/**
	 * @param int|\Sitegear\Ext\Module\Products\Entities\Category $parent
	 *
	 * @return \Sitegear\Ext\Module\Products\Entities\Category[]
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
	 * @return \Sitegear\Ext\Module\Products\Entities\Category
	 */
	public function selectCategoryByUrlPath($urlPath) {
		return $this->_em->createQueryBuilder()
				->select('pc')
				->from('Products:Category', 'pc')
				->where('pc.urlPath = :urlPath')
				->setParameter('urlPath', $urlPath)
				->getQuery()->getSingleResult();
	}

	/**
	 * @param int|\Sitegear\Ext\Module\Products\Entities\Category $category
	 *
	 * @return \Sitegear\Ext\Module\Products\Entities\Item[]
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

	/**
	 * @return \Sitegear\Ext\Module\Products\Entities\Item[]
	 */
	public function getAllActiveItems() {
		return $this->_em->createQueryBuilder()
				->select('pi')
				->from('Products:Item', 'pi')
				->where('pi.active = true')
				->getQuery()->getResult();
	}

	/**
	 * @param string $urlPath
	 *
	 * @return \Sitegear\Ext\Module\Products\Entities\Item
	 */
	public function selectActiveItemByUrlPath($urlPath) {
		return $this->_em->createQueryBuilder()
				->select('pi')
				->from('Products:Item', 'pi')
				->where('pi.urlPath = :urlPath')
				->andWhere('pi.active = true')
				->setParameter('urlPath', $urlPath)
				->getQuery()->getSingleResult();
	}

}
