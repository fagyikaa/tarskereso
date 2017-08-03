<?php

namespace Core\UserBundle\Controller;

use Core\CommonBundle\Exception\AccessDeniedException;
use FOS\RestBundle\Controller\FOSRestController;
use Core\UserBundle\Exception\NotFoundEntityException;
use Core\UserBundle\Form\Type\ChangePasswordType;

class ProfileController extends FOSRestController {

    /**
     * Renders the profile page's skeleton.
     * 
     * @param int $userId
     * @throws NotFoundEntityException
     * @return html
     */
    public function profileSkeletonAction($userId) {
        $view = $this->view()
                ->setTemplateData(array('user' => $this->get('core_user.user_manager')->getUserOr404($userId)))
                ->setTemplate('CoreUserBundle:Profile:profileSkeleton.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

    /**
     * Renders the profile's introduction page
     * 
     * @param int $userId
     * @throws NotFoundEntityException
     * @return html
     */
    public function profileIntroductionAction($userId) {
        $view = $this->view()
                ->setTemplateData(array('user' => $this->get('core_user.user_manager')->getUserOr404($userId)))
                ->setTemplate('CoreUserBundle:Profile:profileIntroduction.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

    /**
     * Renders the profile's ideal page
     * 
     * @param int $userId
     * @throws NotFoundEntityException
     * @return html
     */
    public function profileIdealAction($userId) {
        $view = $this->view()
                ->setTemplateData(array('user' => $this->get('core_user.user_manager')->getUserOr404($userId)))
                ->setTemplate('CoreUserBundle:Profile:profileIdeal.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

    /**
     * Renders the profile's gallery page
     * 
     * @param int $userId
     * @throws NotFoundEntityException
     * @return html
     */
    public function profileGalleryAction($userId) {
        $view = $this->view()
                ->setTemplateData(array('user' => $this->get('core_user.user_manager')->getUserOr404($userId)))
                ->setTemplate('CoreUserBundle:Profile:profileGallery.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

    /**
     * Renders the profile's settings page
     * 
     * @param int $userId
     * @return html
     * @throws AccessDeniedException
     * @throws NotFoundEntityException
     */
    public function profileSettingsAction($userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);
        if ($user !== $this->getUser() && false === $this->isGranted('ROLE_ADMIN_CAN_EDIT_USER_SETTINGS')) {
            throw new AccessDeniedException('user.no_right_to_edit_settings');
        }

        $form = $this->get('form.factory')->create(new ChangePasswordType());

        $view = $this->view()
                ->setTemplateData(array('user' => $user))
                ->setData(array('form' => $form->createView()))
                ->setTemplate('CoreUserBundle:Profile:profileSettings.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

    /**
     * Renders $user profile's delete confirming modal
     * 
     * @return html response
     */
    public function showConfirmDeleteModalAction() {
        $view = $this->view()
                ->setTemplate('CoreUserBundle:Profile:confirmDeleteModal.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

}
