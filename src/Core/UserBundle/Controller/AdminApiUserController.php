<?php

namespace Core\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use Core\CommonBundle\Exception\AccessDeniedException;

class AdminApiUserController extends FOSRestController {

    /**
     * Returns every registered (and not deleted) users include admins in the format dataTables requires.
     * 
     * @param Request $request
     * @return json
     * @throws AccessDeniedException
     */
    public function getAllUsersDataTablesAction(Request $request) {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_ALL_USERS')) {
            throw new AccessDeniedException('user.no_right_to_view_all');
        }

        $parameters = $request->request->all();

        $result = $this->get('core_user.user_manager')->getAllUsersDataTable($parameters);

        $view = $this->view($result);
        return $this->handleView($view);
    }

}
