<?php

namespace Core\UserBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ApiSearchController extends FOSRestController {

    /**
     * Searches for users fit for the given criteries in the form except admins and
     * the current user. Returns those users' id, age, gender, settlement and motto in json.
     * 
     * @return json
     * @throws EntityValidationException
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function searchUsersAction(Request $request, $userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);
        $params = $request->request->all()[$this->get('core_user.form.search')->getName()];
        
        $this->get('core_user.search_manager')->saveUserSearch($user, $params);
        
        unset($params['_token']);
        $result = $this->get('core_user.search_manager')->searchByFiltersExceptAdminsAndGivenOne($userId, $params);
        
        $view = $this->view($result, Response::HTTP_OK);
        return $this->handleView($view);
    }

}
