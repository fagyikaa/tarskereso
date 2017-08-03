<?php

namespace Core\CommonBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Core\CommonBundle\Exception\BadRequestException;

class ApiSecurityController extends FOSRestController {

    /**
     * Authenticates user through websocket. Returns a response containing every information for the authentication.
     * 
     * @param Request $request
     * @return json
     * exceptiooooon micsoda
     */
    public function websocketAuthenticationAction(Request $request) {
        if (false === $request->request->has('authid')) {
            throw new BadRequestException('common.websocket_auth.missing_authid');
        }

        //If authId contains 'impersonating' that means that the logged in user is an admin impersonating somebody
        $authId = $request->request->get('authid');
        if (strpos($authId, 'impersonating') !== false) {
            $userId = substr($authId, 0, -strlen(' impersonating'));
            $impersonating = true;
        } else {
            $userId = $authId;
            $impersonating = false;
        }

        $user = $this->get('core_user.user_manager')->getUserOr404($userId);

        $response = array(
            'secret' => $this->get('hashids')->encode($user->getId()),
            'iterations' => 100,
            'keylen' => 16,
            'role' => 'user',
            'username' => $user->getUsername(),
            'id' => $user->getId(),
            'roles' => $this->get('core_user.role_manager')->getEveryRolesAndSubrolesOfGivenRolesInHierarchy($user->getRoles(), $this->getParameter('security.role_hierarchy.roles')),
            'impersonating' => $impersonating,
            'isAdmin' => $this->get('core_user.role_manager')->isAdmin($user),
        );

        $view = $this->view($response, Response::HTTP_OK);
        return $this->handleView($view);
    }

}
