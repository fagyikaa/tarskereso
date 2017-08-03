<?php

namespace Core\UserBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Core\CommonBundle\Exception\AccessDeniedException;

class AdminUserController extends FOSRestController {

    /**
     * Renders the template for the active users.
     *
     * @return html response
     * @throws AccessDeniedException
     */
    public function renderShowActiveUsersAction() {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_ACTIVE_USERS')) {
            throw new AccessDeniedException('user.no_right_to_view_active');
        }

        $view = $this->view()
                ->setTemplate('CoreUserBundle:AdminUser:showActiveUsers.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

    /**
     * Renders the template for all users.
     *
     * @return html response
     * @throws AccessDeniedException
     */
    public function renderShowAllUsersAction() {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_ALL_USERS')) {
            throw new AccessDeniedException('user.no_right_to_view_all');
        }

        $view = $this->view()
                ->setTemplate('CoreUserBundle:AdminUser:showAllUsers.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

}
