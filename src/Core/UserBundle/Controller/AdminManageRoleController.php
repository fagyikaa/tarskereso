<?php

namespace Core\UserBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Core\CommonBundle\Exception\AccessDeniedException;
use Core\UserBundle\Entity\RoleSet;

class AdminManageRoleController extends FOSRestController {

    /**
     * Renders the edit role template.
     * 
     * @return html response
     * @throws AccessDeniedException
     */
    public function renderEditRoleAction() {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_EDIT_ROLE')) {
            throw new AccessDeniedException('user.role.no_right_to_view_page');
        }

        $view = $this->view()
                ->setTemplate('CoreUserBundle:AdminManageRole:editRole.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

    /**
     * Renders the show role set template.
     * 
     * @return html response
     * @throws AccessDeniedException
     */
    public function renderShowRoleSetsAction() {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_EDIT_ROLE')) {
            throw new AccessDeniedException('user.role.no_right_to_view_page');
        }

        $view = $this->view()
                ->setTemplate('CoreUserBundle:AdminManageRole:showRoleSets.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

    /**
     * Renders the create role set modal template.
     * 
     * @return html response
     * @throws AccessDeniedException
     */
    public function renderCreateRoleSetModalAction() {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_EDIT_ROLE')) {
            throw new AccessDeniedException('user.role.no_right_to_view_page');
        }

        $view = $this->view()
                ->setTemplate('CoreUserBundle:AdminManageRole:createRoleSetModal.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

    /**
     * Renders the create role set form template.
     * 
     * @return html response
     * @throws AccessDeniedException
     */
    public function renderCreateRoleSetFormAction() {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_EDIT_ROLE')) {
            throw new AccessDeniedException('user.role.no_right_to_view_page');
        }

        $form = $this->createForm($this->get('core_user.form.type.role_set'));
        $view = $this->view(array('form' => $form->createView()))
                ->setTemplate('CoreUserBundle:AdminManageRole:createRoleSetForm.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

    /**
     * Renders the detailed role set modal template.
     * 
     * @return html response
     * @throws AccessDeniedException
     */
    public function renderDetailedRoleSetModalAction() {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_EDIT_ROLE')) {
            throw new AccessDeniedException('user.role.no_right_to_view_page');
        }

        $view = $this->view()
                ->setTemplate('CoreUserBundle:AdminManageRole:detailedRoleSetModal.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

    /**
     * Renders the edit role set modal template.
     * 
     * @return html response
     * @throws AccessDeniedException
     * @throws EntityNotFoundException
     */
    public function renderEditRoleSetModalAction($roleSetId) {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_EDIT_ROLE')) {
            throw new AccessDeniedException('user.role.no_right_to_view_page');
        }
        
        $roleSet = $this->get('core_user.role_manager')->getRoleSetOr404($roleSetId, true);

        $view = $this->view(array('roleSetId' => $roleSetId))
                ->setTemplate('CoreUserBundle:AdminManageRole:editRoleSetModal.html.twig')
                ->setFormat('html');
        return $this->handleView($view);
    }

    /**
     * Renders the edit role set form template.
     * 
     * @return html response
     * @throws AccessDeniedException
     */
    public function renderEditRoleSetFormAction($roleSetId) {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_EDIT_ROLE')) {
            throw new AccessDeniedException('user.role.no_right_to_view_page');
        }

        $form = $this->createForm($this->get('core_user.form.type.role_set'), $this->getDoctrine()->getManager()->getRepository('CoreUserBundle:RoleSet')->find($roleSetId));
        
        $view = $this->view(array(
                    'form' => $form->createView(),
                    'roleSetId' => $roleSetId
                ))
                ->setTemplate('CoreUserBundle:AdminManageRole:editRoleSetForm.html.twig')
                ->setFormat('html');        
        return $this->handleView($view);
    }

}
