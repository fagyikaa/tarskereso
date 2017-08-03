<?php

namespace Core\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Core\UserBundle\Entity\UserFriendship;

class UserFriendshipRepository extends EntityRepository {

    /**
     * Searches for UserFriendship between the users with the id of $user1Id and $user2Id.
     * 
     * @param int $user1Id
     * @param int $user2Id
     * @return UserFriendship|null
     */
    public function findFriendshipOfUsers($user1Id, $user2Id) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('u')
                ->from('CoreUserBundle:UserFriendship', 'u')
                ->join('u.requester', 'r')
                ->join('u.invited', 'i')
                ->where('(r.id = :user1Id AND i.id = :user2Id) OR (r.id = :user2Id AND i.id = :user1Id)')
                ->andWhere('r.deletedAt IS NULL AND r.enabled = 1 AND i.deletedAt IS NULL AND i.enabled = 1')
                ->setParameters(array(
                    'user1Id' => $user1Id,
                    'user2Id' => $user2Id,
        ));

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * Searches for UserFriendships which status are BLOCKED and the requester is the user with the id $userId.
     * 
     * @param int $userId
     * @return array
     */
    public function findBlockedFriendshipsByUser($userId) {//findBlockedFriendshipsWhereUserBlocked
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('u')
                ->from('CoreUserBundle:UserFriendship', 'u')
                ->join('u.requester', 'r')
                ->join('u.invited', 'i')
                ->where('r.id = :userId AND u.status = :status')
                ->andWhere('r.deletedAt IS NULL AND r.enabled = 1 AND i.deletedAt IS NULL AND i.enabled = 1')
                ->setParameters(array(
                    'userId' => $userId,
                    'status' => UserFriendship::STATUS_BLOCKED,
        ));

        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Searches for UserFriendships which status are BLOCKED and the invited is the user with the id $userId.
     * 
     * @param int $userId
     * @return array
     */
    public function findBlockedFriendshipsWhereUserBlocked($userId) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('u')
                ->from('CoreUserBundle:UserFriendship', 'u')
                ->join('u.requester', 'r')
                ->join('u.invited', 'i')
                ->where('i.id = :userId AND u.status = :status')
                ->andWhere('i.deletedAt IS NULL AND i.enabled = 1 AND r.deletedAt IS NULL AND r.enabled = 1')
                ->setParameters(array(
                    'userId' => $userId,
                    'status' => UserFriendship::STATUS_BLOCKED,
        ));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Searches for UserFriendships which status are PENDING and the invited is the user with the id $userId
     * and the invitedSeenAt is null.
     * 
     * @param int $userId
     * @return array
     */
    public function findUnseenPendingRequestsForUser($userId) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('u')
                ->from('CoreUserBundle:UserFriendship', 'u')
                ->join('u.requester', 'r')
                ->join('u.invited', 'i')
                ->where('i.id = :userId AND u.status = :status AND u.invitedSeenAt IS NULL')
                ->andWhere('r.deletedAt IS NULL AND r.enabled = 1 AND i.deletedAt IS NULL AND i.enabled = 1')
                ->setParameters(array(
                    'userId' => $userId,
                    'status' => UserFriendship::STATUS_PENDING,
        ));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Searches for UserFriendships which status are PENDING and the invited is the user with the id $userId.
     * 
     * @param int $userId
     * @return array
     */
    public function findPendingRequestsForUser($userId) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('u')
                ->from('CoreUserBundle:UserFriendship', 'u')
                ->join('u.invited', 'i')
                ->join('u.requester', 'r')
                ->where('i.id = :userId AND u.status = :status')
                ->andWhere('r.deletedAt IS NULL AND r.enabled = 1 AND i.deletedAt IS NULL AND i.enabled = 1')
                ->setParameters(array(
                    'userId' => $userId,
                    'status' => UserFriendship::STATUS_PENDING,
        ));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Searches for UserFriendships which status are ACCEPTED and the invited or requester is the user with the id $userId.
     * 
     * @param int $userId
     * @return array
     */
    public function findFriendsForUser($userId) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('u')
                ->from('CoreUserBundle:UserFriendship', 'u')
                ->join('u.requester', 'r')
                ->join('u.invited', 'i')
                ->where('(r.id = :userId OR i.id = :userId) AND u.status = :status')
                ->andWhere('r.deletedAt IS NULL AND r.enabled = 1 AND i.deletedAt IS NULL AND i.enabled = 1')
                ->setParameters(array(
                    'userId' => $userId,
                    'status' => UserFriendship::STATUS_ACCEPTED,
        ));

        return $queryBuilder->getQuery()->getResult();
    }

}
