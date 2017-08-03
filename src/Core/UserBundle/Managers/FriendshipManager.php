<?php

namespace Core\UserBundle\Managers;

use Doctrine\ORM\EntityManagerInterface;
use Core\UserBundle\Entity\User;
use Core\UserBundle\Entity\UserFriendship;
use Core\UserBundle\Managers\RoleManager;
use Core\CommonBundle\Exception\NotFoundEntityException;
use Core\CommonBundle\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Core\UserBundle\Events\UserBundleEvents;
use Core\UserBundle\Events\UserNewFriendshipRequest;

class FriendshipManager {

    protected $em;
    protected $translator;
    protected $roleManager;
    protected $dispatcher;

    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator, RoleManager $roleManager, EventDispatcherInterface $dispatcher) {
        $this->em = $em;
        $this->translator = $translator;
        $this->roleManager = $roleManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * If the $requester and $invited haven't been in a UserFriendship before then creates a new one
     * with status pending. If they were and the status is blocked then throws exception, if declined
     * then sets back to pending and sets $requester to requester and $invited to $invited and acknowledgedAt and
     * invitedSeenAt to null.
     * 
     * @param User $requester
     * @param User $invited
     * @return UserFriendship
     * @throws AccessDeniedException
     */
    public function addFriendForUser(User $requester, User $invited) {
        $friendship = $this->getOrCreateUserFriendshipOfUsers($requester, $invited);

        if ($friendship->getStatus() === UserFriendship::STATUS_BLOCKED) {
            throw new AccessDeniedException('user.add_friend_blocked');
        } elseif (is_null($friendship->getStatus()) || $friendship->getStatus() === UserFriendship::STATUS_DECLINED) {
            $friendship->setRequester($requester);
            $friendship->setInvited($invited);
            $friendship->setStatus(UserFriendship::STATUS_PENDING);
            $friendship->setAcknowledgedAt(null);
            $friendship->setInvitedSeenAt(null);
        } else {
            return $friendship;
        }

        $this->em->persist($friendship);
        $this->em->flush();

        $this->dispatcher->dispatch(UserBundleEvents::USER_NEW_FRIENDSHIP_REQUEST, new UserNewFriendshipRequest($friendship));

        return $friendship;
    }

    /**
     * Gets the UserFriendship of $currentUser and $otherUser or throws NotFoundEntityException if they don't have.
     * Checks if $currentUser is the invited of the UserFriendship and that the status is PENDING and
     * throws AccessDeniedHttpException if not. Sets status to ACCEPTED, acknowledgedAt to now and invitedSeenAt
     * to null.
     * 
     * @param User $currentUser
     * @param User $otherUser
     * @return UserFriendship
     * @throws AccessDeniedException
     * @throws NotFoundEntityException
     */
    public function acceptFriendForUserOr40X(User $currentUser, User $otherUser) {
        $userFriendship = $this->getUserFriendshipOfUsersOr404($currentUser, $otherUser);

        if ($userFriendship->getInvited() !== $currentUser) {
            throw new AccessDeniedException('user.accept_own_request');
        } elseif ($userFriendship->getStatus() !== UserFriendship::STATUS_PENDING) {
            throw new AccessDeniedException('user.only_pending_can_be_accepted');
        }

        $userFriendship->setStatus(UserFriendship::STATUS_ACCEPTED);
        $userFriendship->setAcknowledgedAt(new \DateTime());
        $userFriendship->setInvitedSeenAt(null);
        $this->em->persist($userFriendship);
        $this->em->flush();

        return $userFriendship;
    }

    /**
     * Gets the UserFriendship of $currentUser and $otherUser or throws NotFoundEntityException if they don't have.
     * If status is BLOCKED or DECLINED then throws AccessDeniedHttpException. Sets status to DECLINED, acknowledgedAt
     * and invitedSeenAt to null.
     * 
     * @param User $currentUser
     * @param User $otherUser
     * @return UserFriendship
     * @throws AccessDeniedException
     * @throws NotFoundEntityException
     */
    public function declineFriendForUserOr40X(User $currentUser, User $otherUser) {
        $userFriendship = $this->getUserFriendshipOfUsersOr404($currentUser, $otherUser);

        if ($userFriendship->getStatus() === UserFriendship::STATUS_BLOCKED) {
            throw new AccessDeniedException('user.friendship_blocked');
        } elseif ($userFriendship->getStatus() === UserFriendship::STATUS_DECLINED) {
            throw new AccessDeniedException('user.already_declined');
        }

        $userFriendship->setStatus(UserFriendship::STATUS_DECLINED);
        $userFriendship->setAcknowledgedAt(null);
        $userFriendship->setInvitedSeenAt(null);
        $this->em->persist($userFriendship);
        $this->em->flush();

        return $userFriendship;
    }

    /**
     * Gets the UserFriendship of $currentUser and $otherUser or throws NotFoundEntityException if they don't have.
     * If status is BLOCKED then throws AccessDeniedHttpException. Sets status to BLOCKED, acknowledgedAt
     * and invitedSeenAt to null, requester to $currentUser and invited to $otherUser.
     * 
     * @param User $currentUser
     * @param User $otherUser
     * @return UserFriendship
     * @throws AccessDeniedException
     * @throws NotFoundEntityException
     */
    public function blockFriendForUserOr403(User $currentUser, User $otherUser) {
        $userFriendship = $this->getOrCreateUserFriendshipOfUsers($currentUser, $otherUser);

        if ($userFriendship->getStatus() === UserFriendship::STATUS_BLOCKED) {
            throw new AccessDeniedException('user.friendship_blocked');
        } elseif ($this->roleManager->isAdmin($otherUser)) {
            throw new AccessDeniedException('user.cant_block_admin');
        }

        $userFriendship->setStatus(UserFriendship::STATUS_BLOCKED);
        $userFriendship->setRequester($currentUser);
        $userFriendship->setInvited($otherUser);
        $userFriendship->setAcknowledgedAt(null);
        $userFriendship->setInvitedSeenAt(null);
        $this->em->persist($userFriendship);
        $this->em->flush();

        return $userFriendship;
    }

    /**
     * Gets the UserFriendship of $currentUser and $otherUser or throws NotFoundEntityException if they don't have.
     * If status isn't BLOCKED or $currentUser isn't the requester of the UserFriendship entity then throws AccessDeniedHttpException. 
     * Sets status to DECLINED, acknowledgedAt and invitedSeenAt to null.
     * 
     * @param User $currentUser
     * @param User $otherUser
     * @return UserFriendship
     * @throws AccessDeniedException
     * @throws NotFoundEntityException
     */
    public function unblockFriendForUserOr40X(User $currentUser, User $otherUser) {
        $userFriendship = $this->getUserFriendshipOfUsersOr404($currentUser, $otherUser);

        if ($userFriendship->getStatus() !== UserFriendship::STATUS_BLOCKED) {
            throw new AccessDeniedException('user.friendship_not_blocked');
        } elseif ($userFriendship->getRequester() !== $currentUser) {
            throw new AccessDeniedException('user.unblock_yourself');
        }

        $userFriendship->setStatus(UserFriendship::STATUS_DECLINED);
        $userFriendship->setAcknowledgedAt(null);
        $userFriendship->setInvitedSeenAt(null);
        $this->em->persist($userFriendship);
        $this->em->flush();

        return $userFriendship;
    }

    /**
     * Returns the UserFriendship between the two given user or throw NotFOundEntityException
     * if it's not exists.
     * 
     * @param User $user1
     * @param User $user2
     * @return UserFriendship
     * @throws NotFoundEntityException
     */
    public function getUserFriendshipOfUsersOr404(User $user1, User $user2) {
        $userFriendshipOrNull = $this->em->getRepository('CoreUserBundle:UserFriendship')->findFriendshipOfUsers($user1->getId(), $user2->getId());

        if (is_null($userFriendshipOrNull)) {
            throw new NotFoundEntityException('user.not_found_friendship');
        }

        return $userFriendshipOrNull;
    }

    /**
     * Returns the existing userfriendship of the two given users or if it doesn't exist
     * then creates a new one.
     * 
     * @param User $requester
     * @param User $invited
     * @return UserFriendship
     */
    public function getOrCreateUserFriendshipOfUsers(User $requester, User $invited) {
        $userFriendshipOrNull = $this->em->getRepository('CoreUserBundle:UserFriendship')->findFriendshipOfUsers($requester->getId(), $invited->getId());

        if (is_null($userFriendshipOrNull)) {
            $userFriendshipOrNull = new UserFriendship();
            $userFriendshipOrNull->setRequester($requester);
            $userFriendshipOrNull->setInvited($invited);
        }

        return $userFriendshipOrNull;
    }

    /**
     * Returns the UserFriendships which status are BLOCKED and the requester is $user.
     * 
     * @param User $user
     * @return array
     */
    public function getBlockedFriendshipsForUser(User $user) {
        return $this->em->getRepository('CoreUserBundle:UserFriendship')->findBlockedFriendshipsByUser($user->getId());
    }

    /**
     * Returns the UserFriendships which status are BLOCKED and the invited is $user.
     * 
     * @param User $user
     * @return array
     */
    public function getFriendshipsWhereUserBlocked(User $user) {
        return $this->em->getRepository('CoreUserBundle:UserFriendship')->findBlockedFriendshipsWhereUserBlocked($user->getId());
    }

    /**
     * Returns the UserFriendships which status are PENDING and the invited is $user
     * and the invitedSeenAt is null.
     * 
     * @param User $user
     * @return array
     */
    public function getUnseenPendingRequestsForUser(User $user) {
        return $this->em->getRepository('CoreUserBundle:UserFriendship')->findUnseenPendingRequestsForUser($user->getId());
    }

    /**
     * Returns the UserFriendships which status are PENDING and the invited is $user.
     * 
     * @param User $user
     * @return array
     */
    public function getPendingRequestsForUser(User $user) {
        return $this->em->getRepository('CoreUserBundle:UserFriendship')->findPendingRequestsForUser($user->getId());
    }

    /**
     * Returns the UserFriendships which status are ACCEPTED and the invited or requester is $user.
     * 
     * @param User $user
     * @return array
     */
    public function getFriendsForUser(User $user) {
        return $this->em->getRepository('CoreUserBundle:UserFriendship')->findFriendsForUser($user->getId());
    }

    /**
     * Sets invitedSeenAt to now of those UserFriendships where $user is the invited and the 
     * invitedSeenAt is null.
     * 
     * @param User $user
     * @return null
     */
    public function setUnseenPendingRequestsInvitedSennAtForUser(User $user) {
        foreach ($user->getInvitedFriendships() as $friendship) {
            if (is_null($friendship->getInvitedSeenAt())) {
                $friendship->setInvitedSeenAt(new \DateTime());
                $this->em->persist($friendship);
            }
        }

        $this->em->flush();

        return null;
    }

    /**
     * Set to declined every friendships which were blocked previously and the given
     * $user was the blocked one. If $andFlush is true then flushes the entity manager.
     * 
     * @param User $user
     * @param boolean $andFlush
     */
    public function removeEveryBlockingFromUser(User $user, $andFlush = true) {
        foreach ($this->getFriendshipsWhereUserBlocked($user) as $friendship) {
            $friendship->setStatus(UserFriendship::STATUS_DECLINED);
            $this->em->persist($friendship);
        }

        if ($andFlush) {
            $this->em->flush();
        }
    }

}
