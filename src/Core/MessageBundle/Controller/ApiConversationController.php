<?php

namespace Core\MessageBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializationContext;
use Core\CommonBundle\Exception\AccessDeniedException;

class ApiConversationController extends FOSRestController {

    /**
     * If the current user is not the one with $userId and don't have ROLE_ADMIN_CAN_VIEW_MESSAGES role
     * then throws exception, returns the user's every conversation in list format otherwise.
     * 
     * @param int $userId
     * @return json
     * @throws AccesDeniedException
     * @throws EntityNotFoundException
     */
    public function getConversationListAction($userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);
        if ($user !== $this->getUser() && false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_MESSAGES')) {
            throw new AccessDeniedException('message.no_right_to_view_messages');
        }

        $conversationList = $this->get('core_message.conversation_manager')->getConversationListOfUser($user);

        $view = $this->view($conversationList, Response::HTTP_OK);
        $serializationContext = SerializationContext::create()->setGroups(array('list', 'Default'));
        $view->setSerializationContext($serializationContext);

        return $this->handleView($view);
    }

    /**
     * If the current user's id equals $currentUserId or have ROLE_ADMIN_CAN_VIEW_MESSAGES role
     * then returns the conversation between the two users with the given ids. The result 
     * extended with the other user's username. The already existing conversation will
     * contain (maximum) $length messages shifted by $offset. 
     * 
     * @param int $currentUserId
     * @param int $otherUserId
     * @return json
     * @throws AccesDeniedException
     * @throws EntityNotFoundException
     */
    public function getConversationAction($currentUserId, $otherUserId, $offset = 0, $length = 4) {
        $currentUser = $this->get('core_user.user_manager')->getUserOr404($currentUserId);
        $otherUser = $this->get('core_user.user_manager')->getUserOr404($otherUserId);
        if ($currentUser !== $this->getUser() && false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_MESSAGES')) {
            throw new AccessDeniedException('message.no_right_to_view_messages');
        }

        $result = array(
            'otherUsername' => $otherUser->getUsername(),
            'conversation' => $this->get('core_message.conversation_manager')->getOrCreateConversationForUsers($currentUser, $otherUser, intval($offset), intval($length)),
        );

        $view = $this->view($result, Response::HTTP_OK);
        $serializationContext = SerializationContext::create()->setGroups(array('Default', 'detailed'));
        $view->setSerializationContext($serializationContext);

        return $this->handleView($view);
    }

    /**
     * If no users exists with the ids in the request parameter under currentUserId or otherUserId
     * or if the user with currentUserId not equals with the current user then throws exception.
     * Set every messages' recieverSeenAt to now in the conversation between the two users where
     * the messages' reciever is the current user and recieverSeenAt is null.
     * 
     * @param Request $request
     * @return json
     * @throws AccessDeniedException
     * @throws EntityNotFoundException
     */
    public function setConversationMessagesSeenAtAction(Request $request) {
        $currentUser = $this->get('core_user.user_manager')->getUserOr404($request->request->get('currentUserId'));
        $otherUser = $this->get('core_user.user_manager')->getUserOr404($request->request->get('otherUserId'));
        if ($currentUser !== $this->getUser()) {
            throw new AccessDeniedException('message.messages_seen_at_only_for_self');
        }

        $this->get('core_message.conversation_manager')->setConversationMessagesSeenAtForCurrentUser($currentUser, $otherUser);

        $view = $this->view(null, Response::HTTP_NO_CONTENT);
        return $this->handleView($view);
    }

    /**
     * If no users exists with the ids if currentUserId or otherUserId or if the user with currentUserId 
     * not equals with the current user and the current user doesn't have ROLE_ADMIN_CAN_VIEW_MESSAGES role
     * then throws exception. Returns the conversation between the two users in list format (messages not included
     * just the last one) otherwise.
     * 
     * @param int $currentUserId
     * @param int $conversationId
     * @return json
     * @throws AccessDeniedException
     * @throws EntityNotFoundException
     */
    public function getConversationForListAction($currentUserId, $conversationId) {
        $currentUser = $this->get('core_user.user_manager')->getUserOr404($currentUserId);
        $conversation = $this->get('core_message.conversation_manager')->getConversationOr404($conversationId);
        if ($currentUser !== $this->getUser() && false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_MESSAGES')) {
            throw new AccessDeniedException('message.no_right_to_view_messages');
        } elseif ($currentUser !== $conversation->getReciever() && $currentUser !== $conversation->getStarter()) {
            throw new AccessDeniedException('message.user_not_part_of_conversation');
        }

        $conversationForList = $this->get('core_message.conversation_manager')->extendConversationWithPartnerDatasForList($conversation, $currentUser);

        $view = $this->view($conversationForList, Response::HTTP_OK);
        $serializationContext = SerializationContext::create()->setGroups(array('list', 'Default'));
        $view->setSerializationContext($serializationContext);

        return $this->handleView($view);
    }

    /**
     * Returns the count of conversations of the current user with messages unseen by them.
     * 
     * @return json
     */
    public function getConversationCountWithUnseenMessageAction() {
        $count = $this->get('core_message.conversation_manager')->getConversationCountWithUnseenMessageForUser($this->getUser());

        $view = $this->view($count, Response::HTTP_OK);

        return $this->handleView($view);
    }

}
