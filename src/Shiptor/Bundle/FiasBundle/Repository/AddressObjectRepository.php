<?php

namespace Shiptor\Bundle\FiasBundle\Repository;

use Doctrine\ORM\Query;

/**
 * AddressObjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AddressObjectRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param \DateTime|null $date
     * @param boolean|null   $actual
     * @param integer|null   $limit
     * @param integer|null   $type
     * @return Query
     */
    public function getPageQuery($actual = null, $type = null, \DateTime $date = null, $limit = null)
    {
        $query = $this
            ->createQueryBuilder('ao')
            ->orderBy('ao.updateDate', 'DESC')
            ->orderBy('ao.aoId', 'DESC');

        if (null !== $date) {
            $query
                ->andWhere('ao.updateDate <= :date')
                ->setParameter('date', $date);
        }

        if (null !== $actual) {
            $query
                ->andWhere('ao.actStatus = :actual')
                ->setParameter('actual', $actual);
        }

        if (null !== $type) {
            $query
                ->andWhere('ao.divType = :type')
                ->setParameter('type', $type);
        }

        if (null === $limit) {
            $limit = 100;
        }

        $query->setMaxResults($limit);

        return $query->getQuery();
    }
}
