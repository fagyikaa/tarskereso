<?php

namespace Core\MessageBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ConversationRepository extends EntityRepository {

    /**
     * If two users with the given ids have already started a conversation then returns it,
     * returns null otherwise.
     * 
     * @param int $user1Id
     * @param int $user2Id
     * @return Conversation|null
     */
    public function findConversationOfUsers($user1Id, $user2Id) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('c')
                ->from('CoreMessageBundle:Conversation', 'c')
                ->join('c.starter', 's')
                ->join('c.reciever', 'r')
                ->where('(s.id = :user1Id AND r.id = :user2Id) OR (s.id = :user2Id AND r.id = :user1Id)')
                ->andWhere('r.deletedAt IS NULL AND r.enabled = 1 AND s.deletedAt IS NULL AND s.enabled = 1')
                ->setParameters(array(
                    'user1Id' => $user1Id,
                    'user2Id' => $user2Id,
        ));

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
    
    /**
     * Returns every conversation of the user with the id of $userId in an array
     * or empty array.
     * 
     * @param int $userId
     * @return array
     */
    public function findConversationsOfUser($userId) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('c as conversation')
                ->from('CoreMessageBundle:Conversation', 'c')
                ->join('c.starter', 's')
                ->join('c.reciever', 'r')
                ->where('s.id = :userId OR r.id = :userId')
                ->andWhere('r.deletedAt IS NULL AND r.enabled = 1 AND s.deletedAt IS NULL AND s.enabled = 1')
                ->setParameter('userId', $userId);

        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Returns the count of conversations which contain messages which are unseen by 
     * the user with the id of $userId.
     * 
     * @param int $userId
     * @return int
     */
    public function getConversationCountWithUnseenMessage($userId) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('COUNT(DISTINCT(c))')
                ->from('CoreMessageBundle:Conversation', 'c')
                ->join('c.starter', 's')
                ->join('c.reciever', 'r')
                ->join('c.messages', 'm', 'WITH', 'm.author <> :userId AND m.recieverSeenAt IS NULL')
                ->where('s.id = :userId OR r.id = :userId')
                ->andWhere('r.deletedAt IS NULL AND r.enabled = 1 AND s.deletedAt IS NULL AND s.enabled = 1')
                ->setParameter('userId', $userId);

        return intval($queryBuilder->getQuery()->getSingleScalarResult());
    }
}
