<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\News;

use Doctrine\ORM\EntityRepository;

/**
 * Custom entity repository for the News module.
 */
class NewsRepository extends EntityRepository {

	/**
	 * Retrieve the total number of headlines currently available.
	 *
	 * @return integer
	 */
	public function getItemCount() {
		return intval($this->_em->createQueryBuilder()
				->select('count(ni)')
				->from('News:Item', 'ni')
				->getQuery()->getSingleScalarResult());
	}

	/**
	 * Retrieve the latest headlines, up to the given limit.
	 *
	 * @param integer $itemLimit
	 *
	 * @return array
	 */
	public function selectLatestItems($itemLimit) {
		$query = $this->_em->createQueryBuilder()
				->select('ni')
				->from('News:Item', 'ni')
				->orderBy('ni.datePublished', 'desc')
				->getQuery();
		if ($itemLimit > 0) {
			$query->setMaxResults($itemLimit);
		}
		return $query->getResult();
	}

	/**
	 * Retrieve a single news item from the backend.
	 *
	 * @param string $urlPath URL path of the item to retrieve.
	 *
	 * @return \Sitegear\Ext\Module\News\Entities\Item News item entity object.
	 */
	public function selectItemByUrlPath($urlPath) {
		return $this->_em->createQueryBuilder()
				->select('ni')
				->from('News:Item', 'ni')
				->where('ni.urlPath = :urlPath')
				->setParameter('urlPath', $urlPath)
				->getQuery()->getSingleResult();
	}

}
