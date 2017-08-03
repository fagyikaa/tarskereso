<?php

namespace Core\UserBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends FOSRestController {

    public function indexAction() {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // redirect authenticated users to homepage
            $view = $this->routeRedirectView('core_common_homepage', array(), Response::HTTP_MOVED_PERMANENTLY);
        } else {
            $view = $this->view()
                    ->setTemplate('CoreUserBundle::layout.html.twig')
                    ->setFormat('html');
        }
        
        return $this->handleView($view);
    }

    public function renderLogin(array $data) {
        $view = $this->view()
                ->setTemplate('FOSUserBundle:Security:login.html.twig')
                ->setTemplateData($data)
                ->setFormat('html');

        return $this->handleView($view);
    }

}
