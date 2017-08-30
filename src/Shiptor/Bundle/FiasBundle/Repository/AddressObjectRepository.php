<?php

namespace Shiptor\Bundle\FiasBundle\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Ramsey\Uuid\Uuid;
use Shiptor\Bundle\FiasBundle\Entity\AddressObject;
use Shiptor\Bundle\FiasBundle\Entity\AddressObjectType;

/**
 * AddressObjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AddressObjectRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param int|null $actual
     * @param int      $offset
     * @param int|null $limit
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getGroupedAddresses($actual = null, $offset = 0, $limit = null)
    {
        $query = $this
            ->createQueryBuilder('ao')
            ->select('ao.postalCode')
            ->where('ao.postalCode IS NOT NULL')
            ->groupBy('ao.postalCode')
            ->orderBy('ao.postalCode', 'ASC');

        if (null !== $actual) {
            $query
                ->andWhere('ao.actStatus = :actual')
                ->setParameter('actual', $actual);
        }

        if (null !== $limit) {
            if ($limit > 100000) {
                $limit = 100000;
            }

            $query
                ->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $query;
    }

    /**
     * @param \DateTime|null $date
     * @param boolean|null   $actual
     * @param integer|null   $offset
     * @param integer|null   $limit
     * @param integer|null   $type
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAddressObject($actual = null, $type = null, \DateTime $date = null, $offset = 0, $limit = null)
    {
        $query = $this
            ->createQueryBuilder('ao')
            ->select('ao, objectType')
            ->leftJoin('ao.shortName', 'objectType')
            ->where('ao.shortName = objectType.scName')
            ->andWhere('ao.aoLevel = objectType.level')
            ->andWhere('LENGTH(ao.plainCode) <= 11')
            ->orderBy('ao.aoLevel', 'ASC')
            ->addOrderBy('ao.aoId', 'DESC');

        if (null !== $date) {
            $query
                ->andWhere('ao.updateDate >= :date')
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

        if (null !== $limit) {
            if ($limit > 100000) {
                $limit = 100000;
            }

            $query
                ->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $query;
    }

    /**
     * @param \DateTime|null $date
     * @param boolean|null   $actual
     * @param integer|null   $type
     * @return Query
     */
    public function getPageQuery($actual = null, $type = null, \DateTime $date = null)
    {
        $query = $this
            ->createQueryBuilder('ao')
            ->orderBy('ao.updateDate', 'DESC')
            ->addOrderBy('ao.aoId', 'DESC');

        if (null !== $date) {
            $query
                ->andWhere('ao.updateDate >= :date')
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

        return $query->getQuery();
    }

    public function getNextId($id)
    {
        return $this
            ->createQueryBuilder('ao')
            ->andWhere('ao.aoId = :id')
            ->setParameter('id', $id);
    }

    /**
     * @param array $item
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getActualAddress($item)
    {
        return $this->createQueryBuilder('ao')
            ->select('ao, objectType')
            ->leftJoin('ao.shortName', 'objectType')
            ->where('ao.shortName = objectType.scName')
            ->andWhere('LENGTH(ao.plainCode) <= 11')
            ->andWhere('ao.offName = :offName')
            ->andWhere('ao.aoLevel = :aoLevel')
            ->andWhere('ao.shortName = :shortName')
            ->andWhere('ao.plainCode = :plainCode')
            ->andWhere('ao.actStatus = :actStatus')
            ->setParameter('offName', $item['offName'])
            ->setParameter('aoLevel', $item['aoLevel'])
            ->setParameter('shortName', $item['shortName'])
            ->setParameter('plainCode', $item['plainCode'])
            ->setParameter('actStatus', AddressObject::STATUS_ACTUAL)
            ->orderBy('ao.updateDate', 'DESC')
            ->addOrderBy('ao.aoId');
    }

    /**
     * @param int  $offset
     * @param null $limit
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAddressParents($offset = 0, $limit = null)
    {
        $qb = $this
            ->createQueryBuilder('ao');

        $query1 = $this
            ->createQueryBuilder('ao1')
            ->select('ao1.parentGuid')
            ->andWhere('LENGTH(ao1.plainCode) <= 11')
            ->andWhere('ao1.actStatus = :actual')
            ->setParameter('actual', AddressObject::STATUS_ACTUAL)
            ->getQuery()
            ->getDQL();

        $query = $qb
            ->select('ao, objectType')
            ->leftJoin('ao.shortName', 'objectType')
            ->where('ao.shortName = objectType.scName')
            ->andWhere('ao.aoLevel = objectType.level')
            ->andWhere('ao.actStatus = :actual')
            ->andWhere('ao.plainCode IS NULL')
            ->andWhere($qb->expr()->in('ao.aoGuid', $query1))
            ->setParameter('actual', AddressObject::STATUS_ACTUAL)
            ->orderBy('ao.aoLevel', 'ASC')
            ->addOrderBy('ao.aoId', 'DESC');

        if (null !== $limit) {
            if ($limit > 100000) {
                $limit = 100000;
            }

            $query
                ->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $query;
    }

    public function getParentAddress($actual = null, $addressGuid)
    {
        $qb = $this
            ->createQueryBuilder('ao');

        $qb = $qb->leftJoin('ao.shortName', 'objectType')
            ->where('ao.shortName = objectType.scName')
            ->andWhere($qb->expr()->orX($qb->expr()->lte('LENGTH(ao.plainCode)', 11), $qb->expr()->isNull('ao.plainCode')))
            ->andWhere('ao.aoGuid = :aoGuid')
            ->setParameter('aoGuid', $addressGuid)
            ->orderBy('ao.updateDate', 'DESC')
            ->addOrderBy('ao.aoId');

        if (null !== $actual) {
            $qb
                ->andWhere('ao.actStatus = :actStatus')
                ->setParameter('actStatus', AddressObject::STATUS_ACTUAL);
        }

        return $qb;
    }

    /**
     * @param $postalCode
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAddressByPostalCode($postalCode)
    {
        $qb = $this
            ->createQueryBuilder('ao');

        $query1 = $this
            ->createQueryBuilder('ao1')
            ->select('ao1.ctArCode')
            ->select('CONCAT(ao1.regionCode, ao1.areaCode, ao1.cityCode, ao1.ctArCode)')
            ->andWhere('ao1.actStatus = :actStatus')
            ->andWhere('ao1.currStatus = :currStatus')
            ->setParameter('actStatus', AddressObject::STATUS_ACTUAL)
            ->setParameter('currStatus', 0)
            ->groupBy('ao1.regionCode')
            ->addGroupBy('ao1.areaCode')
            ->addGroupBy('ao1.cityCode')
            ->addGroupBy('ao1.ctArCode')
            ->getQuery()
            ->getDQL();

        return $qb
            ->select('ao, objectType')
            ->leftJoin('ao.shortName', 'objectType')
            ->where('ao.shortName = objectType.scName')
            ->andWhere('ao.aoLevel = objectType.level')
            ->andWhere('ao.postalCode = :postalCode')
            ->setParameter('actStatus', AddressObject::STATUS_ACTUAL)
            ->setParameter('currStatus', 0)
            ->setParameter('postalCode', $postalCode)
            ->andWhere($qb->expr()->in('ao.plainCode', $query1))
            ->orderBy('ao.aoLevel', 'ASC')
            ->addOrderBy('ao.aoId', 'DESC');
    }
}
