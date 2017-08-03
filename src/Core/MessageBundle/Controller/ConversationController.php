<?php

namespace Core\MessageBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Core\CommonBundle\Exception\AccessDeniedException;

class ConversationController extends FOSRestController {

    /**
     * Renders the show conversations page.
     * 
     * @param int $userId
     * @return html
     * @throws AccesDeniedException
     * @throws NotFoundEntityException
     */
    public function showConversationsAction($userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);
        if ($user !== $this->getUser() && false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_MESSAGES')) {
            throw new AccessDeniedException('message.no_right_to_view_messages');
        }

        $view = $this->view(null, Response::HTTP_OK)
                ->setTemplate('CoreMessageBundle:Conversation:showConversations.html.twig')
                ->setTemplateData(array(
                    'user' => $user,
                ))
                ->setFormat('html');
        return $this->handleView($view);
    }

}
