<?php

namespace Core\UserBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends FOSRestController {

    /**
     * Renders search page
     * 
     * @return html response
     */
    public function searchAction() {
        $form = $this->createForm($this->get('core_user.form.search'), $this->get('core_user.search_manager')->getUserSearchForUser($this->getUser()));

        $view = $this->view(null, Response::HTTP_OK)
                ->setTemplate('CoreUserBundle:Search:search.html.twig')
                ->setData(array('form' => $form))
                ->setFormat('html');
        return $this->handleView($view);
    }

}
