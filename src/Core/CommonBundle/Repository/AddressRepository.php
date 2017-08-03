<?php

namespace Core\CommonBundle\Repository;

use Doctrine\ORM\EntityRepository;

class AddressRepository extends EntityRepository {

    /**
     * Returns the count of every Address entity in the database.
     * 
     * @return int
     */
    public function getCountOfAddresses() {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $count = $queryBuilder->select('COUNT(a.id)')
                ->from('CoreCommonBundle:Address', 'a')
                ->getQuery()
                ->getSingleScalarResult();

        return intval($count);
    }

    /**
     * Returns every Address's settlement and id order by settlement DESC.
     * 
     * @return array
     */
    public function getNameAndIdForAll() {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $settlements = $queryBuilder->select('a.id, a.settlement')
                ->from('CoreCommonBundle:Address', 'a')
                ->orderBy('a.settlement', 'DESC')
                ->getQuery()
                ->getArrayResult();

        return $settlements;
    }

    /**
     * Returns every Address's county.
     * 
     * return array
     */
    public function getCountyForAll() {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $counties = $queryBuilder->select('DISTINCT(a.county)')
                ->from('CoreCommonBundle:Address', 'a')
                ->getQuery()
                ->getScalarResult();

        $oneDimArr = array_map('current', $counties);
        return array_combine($oneDimArr, $oneDimArr);
    }

}
