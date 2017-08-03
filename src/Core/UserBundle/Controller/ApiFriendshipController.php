<?php

namespace Core\UserBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializationContext;
use Core\CommonBundle\Exception\AccessDeniedException;

class ApiFriendshipController extends FOSRestController {

    /**
     * Gets the user with the userId of the request userId parameter. If the current user and the
     * retrieved haven't been in a UserFriendship before then creates a new one with status pending. 
     * If they were and the status is blocked then throws exception, if declined then sets back to pending
     * and sets acknowledgedAt and invitedSeenAt to null. In case of the friendship is already pending return
     * null, returns the firendship otherwise.
     * 
     * @param Request $request
     * @return json
     * @throws NotFoundEntityException
     * @throws AccessDeniedException
     */
    public function addFriendAction(Request $request) {
        $user = $this->get('core_user.user_manager')->getUserOr404($request->request->get('userId'));
        $friendship = $this->get('core_user.friendship_manager')->addFriendForUser($this->getUser(), $user);

        $view = $this->view($friendship, Response::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Gets the user with the userId of the request userId parameter. Gets the UserFriendship of 
     * the current user and the retrieved or throws NotFoundEntityException if they don't have.
     * Checks if current user is the invited of the UserFriendship and that the status is PENDING and
     * throws AccessDeniedHttpException if not. Sets status to ACCEPTED, acknowledgedAt to now and invitedSeenAt
     * to null.
     * 
     * @param User $currentUser
     * @param User $otherUser
     * @return json
     * @throws AccessDeniedException
     * @throws NotFoundEntityException
     */
    public function acceptFriendAction(Request $request) {
        $user = $this->get('core_user.user_manager')->getUserOr404($request->request->get('userId'));
        $friendship = $this->get('core_user.friendship_manager')->acceptFriendForUserOr40X($this->getUser(), $user);

        $view = $this->view($friendship, Response::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Gets the user with the userId of the request userId parameter. Gets the UserFriendship of 
     * the current user and the retrieved or throws NotFoundEntityException if they don't have.
     * If status is BLOCKED or DECLINED then throws AccessDeniedHttpException. Sets status to DECLINED, acknowledgedAt
     * and invitedSeenAt to null.
     * 
     * @param Request $request
     * @return json
     * @throws AccessDeniedException
     * @throws NotFoundEntityException
     */
    public function declineFriendAction(Request $request) {
        $user = $this->get('core_user.user_manager')->getUserOr404($request->request->get('userId'));
        $friendship = $this->get('core_user.friendship_manager')->declineFriendForUserOr40X($this->getUser(), $user);

        $view = $this->view($friendship, Response::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Gets the user with the userId of the request userId parameter. Gets the UserFriendship of 
     * the current user and the retrieved or throws NotFoundEntityException if they don't have.
     * If status is BLOCKED then throws AccessDeniedHttpException. Sets status to BLOCKED, acknowledgedAt
     * and invitedSeenAt to null, requester to the current user and invited to the retrieved one.
     * 
     * @param Request $request
     * @return json
     * @throws AccessDeniedException
     * @throws NotFoundEntityException
     */
    public function blockFriendAction(Request $request) {
        $user = $this->get('core_user.user_manager')->getUserOr404($request->request->get('userId'));
        $friendship = $this->get('core_user.friendship_manager')->blockFriendForUserOr403($this->getUser(), $user);

        $view = $this->view($friendship, Response::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Gets the user with the userId of the request userId parameter. Gets the UserFriendship of 
     * the current user and the retrieved or throws NotFoundEntityException if they don't have.
     * If status isn't BLOCKED or the current user isn't the requester of the UserFriendship entity then
     * throws AccessDeniedHttpException. Sets status to DECLINED, acknowledgedAt and invitedSeenAt to null.
     * 
     * @param Request $request
     * @return json
     * @throws AccessDeniedException
     * @throws NotFoundEntityException
     */
    public function unblockFriendAction(Request $request) {
        $user = $this->get('core_user.user_manager')->getUserOr404($request->request->get('userId'));
        $friendship = $this->get('core_user.friendship_manager')->unblockFriendForUserOr40X($this->getUser(), $user);

        $view = $this->view($friendship, Response::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Gets the user with the userId of the request userId parameter. Returns the UserFriendship of 
     * the current user and the retrieved or throws NotFoundEntityException if they don't have.
     * 
     * @param int $userId
     * @return json
     * @throws NotFoundEntityException
     */
    public function getFriendshipWithAction($userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);
        $friendship = $this->get('core_user.friendship_manager')->getUserFriendshipOfUsersOr404($this->getUser(), $user);

        $view = $this->view($friendship, Response::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Gets the user with $userId or throws EntityNotFoundException. If the current user not equals
     * to the retrieved one and doesn't have ROLE_ADMIN_CAN_VIEW_FRIENDS role then throws AccessDeniedException.
     * Returns the UserFriendships of the user which are ACCEPTED extended with the ids of the users.
     * 
     * @param int $userId
     * @return json
     * @throws AccessDeniedException
     */
    public function getFriendsAction($userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);
        if ($user !== $this->getUser() && false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_FRIENDS')) {
            throw new AccessDeniedException('user.no_right_to_view_friends');
        }

        $friends = $this->get('core_user.friendship_manager')->getFriendsForUser($user);

        $view = $this->view($friends, Response::HTTP_OK);
        $serializationContext = SerializationContext::create()->setGroups(array('detailed'));
        $view->setSerializationContext($serializationContext);

        return $this->handleView($view);
    }

    /**
     * Gets the user with $userId or throws EntityNotFoundException. If the current user not equals
     * to the retrieved one and doesn't have ROLE_ADMIN_CAN_VIEW_FRIENDS role then throws AccessDeniedException.
     * Returns the UserFriendships which status are PENDING and the invited is the retrieved user extended with the ids of the users.
     * 
     * @param int $userId
     * @return json
     * @throws AccessDeniedException
     */
    public function getPendingRequestsAction($userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);
        if ($user !== $this->getUser() && false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_FRIENDS')) {
            throw new AccessDeniedException('user.no_right_to_view_friends');
        }

        $friends = $this->get('core_user.friendship_manager')->getPendingRequestsForUser($user);

        $view = $this->view($friends, Response::HTTP_OK);
        $serializationContext = SerializationContext::create()->setGroups(array('detailed'));
        $view->setSerializationContext($serializationContext);

        return $this->handleView($view);
    }

    /**
     * Gets the user with $userId or throws EntityNotFoundException. If the current user not equals
     * to the retrieved one and doesn't have ROLE_ADMIN_CAN_VIEW_FRIENDS role then throws AccessDeniedException.
     * Returns the UserFriendships which status are BLOCKED and the requester is the retrieved user extended with the ids of the users.
     * 
     * @param int $userId
     * @return json
     * @throws AccessDeniedException
     */
    public function getBlockedFriendshipsAction($userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);
        if ($user !== $this->getUser() && false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_FRIENDS')) {
            throw new AccessDeniedException('user.no_right_to_view_friends');
        }

        $friendships = $this->get('core_user.friendship_manager')->getBlockedFriendshipsForUser($user);

        $view = $this->view($friendships, Response::HTTP_OK);
        $serializationContext = SerializationContext::create()->setGroups(array('detailed'));
        $view->setSerializationContext($serializationContext);

        return $this->handleView($view);
    }

    /**
     * Gets the user with $userId or throws EntityNotFoundException. If the current user not equals
     * to the retrieved one and doesn't have ROLE_ADMIN_CAN_VIEW_FRIENDS role then throws AccessDeniedException.
     * Returns the UserFriendships which status are PENDING and the invited is the retrieved user
     * and the invitedSeenAt is null extended with the ids of the users.
     * 
     * @param int $userId
     * @return json
     * @throws AccessDeniedException
     */
    public function getUnseenPendingRequestsAction($userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);
        if ($user !== $this->getUser() && false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_FRIENDS')) {
            throw new AccessDeniedException('user.no_right_to_view_friends');
        }

        $unseenPendingRequests = $this->get('core_user.friendship_manager')->getUnseenPendingRequestsForUser($user);

        $view = $this->view($unseenPendingRequests, Response::HTTP_OK);
        $serializationContext = SerializationContext::create()->setGroups(array('detailed'));
        $view->setSerializationContext($serializationContext);

        return $this->handleView($view);
    }

    /**
     * Sets invitedSeenAt to now of the UserFriendships of the current user which
     * invitedSeenAt are null.
     * 
     * @return json
     */
    public function setUnseenPendingRequestsInvitedSeenAtAction() {
        $this->get('core_user.friendship_manager')->setUnseenPendingRequestsInvitedSennAtForUser($this->getUser());

        $view = $this->view(null, Response::HTTP_NO_CONTENT);

        return $this->handleView($view);
    }

}
