<?php

namespace Core\UserBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Core\CommonBundle\Exception\AccessDeniedException;

class FriendshipController extends FOSRestController {

    /**
     * Renders show friends page and checks that the given $userId belongs to the current
     * user or the current user is an admin with role ROLE_ADMIN_CAN_VIEW_FRIENDS.
     * 
     * @param int userId
     * @return html response
     * @throws AccessDeniedException
     * @@throws EntityNotFoundException
     */
    public function showFriendsAction($userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);
        if ($user !== $this->getUser() && false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_FRIENDS')) {
            throw new AccessDeniedException('user.no_right_to_view_friends');
        }

        $view = $this->view(null, Response::HTTP_OK)
                ->setTemplate('CoreUserBundle:Friendship:showFriends.html.twig')
                ->setTemplateData(array(
                    'user' => $user,
                ))
                ->setFormat('html');
        return $this->handleView($view);
    }

}
