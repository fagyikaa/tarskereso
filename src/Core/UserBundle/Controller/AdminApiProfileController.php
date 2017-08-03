<?php

namespace Core\UserBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Core\CommonBundle\Exception\NotFoundEntityException;
use Core\CommonBundle\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

class AdminApiProfileController extends FOSRestController {

    /**
     * If the admin has ROLE_ADMIN_CAN_EDIT_USER_SETTINGS role and the user is exists
     * with the id of userId request parameter (and isn't the current user) then sets the user's enabled property 
     * to isEnabled request parameter and returns true.
     * 
     * @param Request $request
     * @return json
     * @throws AccessDeniedException
     * @throws NotFoundEntityException
     * @throws EntityValidationException
     */
    public function editUserEnabledAction(Request $request) {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_EDIT_USER_SETTINGS')) {
            throw new AccessDeniedException('user.no_right_to_edit_profile');
        }

        $user = $this->get('core_user.user_manager')->getUserOr404($request->request->get('userId'));
        if ($user === $this->getUser()) {
            throw new AccessDeniedException('user.set_enabled_yourself');
        }

        $this->get('core_user.user_manager')->setUserEnabled($user, $request->request->get('isEnabled'));

        $view = $this->view(true, Response::HTTP_OK);
        return $this->handleView($view);
    }

}
