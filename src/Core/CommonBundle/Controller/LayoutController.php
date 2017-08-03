<?php

namespace Core\CommonBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;

class LayoutController extends FOSRestController {

    /**
     * Renders layout's index page
     * 
     * @return html response
     */
    public function indexAction() {
        $view = $this->view(null, Response::HTTP_OK)
                ->setTemplate('CoreCommonBundle:Layout:index.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

    /**
     * Renders layout's header
     * 
     * @return html response
     */
    public function headerAction() {
        $view = $this->view(null, Response::HTTP_OK)
                ->setTemplate('CoreCommonBundle:Layout:header.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

    /**
     * Renders sidebar
     * 
     * @return html response
     */
    public function sidebarAction() {
        $view = $this->view(null, Response::HTTP_OK)
                ->setTemplate('CoreCommonBundle:Layout:sidebar.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

    /**
     * Renders header
     * 
     * @return html response
     */
    public function pageHeadAction() {
        $view = $this->view(null, Response::HTTP_OK)
                ->setTemplate('CoreCommonBundle:Layout:pageHead.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

    /**
     * Renders footer
     * 
     * @return html response
     */
    public function pageFooterAction() {
        $view = $this->view(null, Response::HTTP_OK)
                ->setTemplate('CoreCommonBundle:Layout:pageFooter.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

    /**
     * Renders idle timeout's modal
     * 
     * @return html response
     */
    public function idleTimeoutModalAction() {
        $view = $this->view(null, Response::HTTP_OK)
                ->setTemplate('CoreCommonBundle:Layout:idleTimeoutModal.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

}
