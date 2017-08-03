<?php

namespace Core\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

class RoleSetRepository extends EntityRepository {

    /**
     * Searches for RoleSets which deletedAt property is null.
     *
     * @return array
     */
    public function findAllUndeleted() {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('r')
                ->from('CoreUserBundle:RoleSet', 'r')
                ->where('r.deletedAt IS NULL');

        return $queryBuilder->getQuery()->getResult();
    }

}
