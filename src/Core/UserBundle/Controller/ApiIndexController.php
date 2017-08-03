<?php

namespace Core\UserBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;

class ApiIndexController extends FOSRestController {

    public function getActiveUsersByGenderAction() {
        $activeUsersByGender = $this->getDoctrine()->getRepository('CoreUserBundle:User')->findAllActiveByGender();

        $view = $this->view($activeUsersByGender, Response::HTTP_OK)
                ->setFormat('json');
        return $this->handleView($view);
    }

}
