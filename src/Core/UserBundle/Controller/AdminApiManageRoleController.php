<?php

namespace Core\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use Core\CommonBundle\Exception\AccessDeniedException;

class AdminApiManageRoleController extends FOSRestController {

    /**
     * If the current user doesn't have the requested role or the user with the given id equals to the current user then deny access.
     * Returns the RoleSets with the translation of the contained roles and the appropriate formatted json for IVHTreeView containing every role. 
     * Also those roles (nodes) which the user has will be flagged with selected = true to be checked on the frontend.  
     * 
     * @param integer $userId
     * @return json
     * @throws AccessDeniedException
     * @throws BadRequestException
     */
    public function getRoleTreeAndSetsForUserAction($userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);

        if (false === $this->isGranted('ROLE_ADMIN_CAN_EDIT_ROLE')) {
            throw new AccessDeniedException('user.role.no_right_to_edit');
        } elseif ($user === $this->getUser()) {
            throw new AccessDeniedException('user.role.no_self');
        }

        $roleManager = $this->get('core_user.role_manager');

        $sanitizedRoleHierarchy = $roleManager->removeRolesFromHierarchy(array('ROLE_USER'));
        $deepRoleHierarchy = $roleManager->getDeepRoleHierarchy(null, $sanitizedRoleHierarchy);

        $roleSetsWithTranslation = $roleManager->getRoleSetsWithTranslation();
        $roleTree = $this->get('core_user.role_handling_front_end_adapter')->getRoleTreeForFrontEnd($deepRoleHierarchy, $user->getRoles());

        $view = $this->view(array(
            'roleTree' => $roleTree,
            'roleSets' => $roleSetsWithTranslation,
                ), Response::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * If the current user doesn't have the requested role or the user with the given id is not an admin, 
     * or equals to the current user then deny access. If data is empty then sets the user's roles to ROLE_ADMIN.
     * If data isn't empty then removes fake roles, ROLE_ADMIN, ROLE_SUPER_ADMIN and ROLE_USER from it then sets
     * the user's roles to the highest ones of the remained roles according to the role hierarchy.
     * 
     * @param Request $request
     * @param integer $userId
     * @return json
     * @throws AccessDeniedException
     * @throws EntityNotFoundException
     */
    public function editUserRolesAction(Request $request, $userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);
        if (false === $this->isGranted('ROLE_ADMIN_CAN_EDIT_ROLE')) {
            throw new AccessDeniedException('user.role.no_right_to_edit');
        } elseif ($user === $this->getUser()) {
            throw new AccessDeniedException('user.role.no_self');
        }

        $this->get('core_user.role_manager')->updateUserRoles($user, $request->request->all());

        return $this->handleView($this->view(null, Response::HTTP_NO_CONTENT));
    }

    /**
     * Return all of the RoleSets including the translations of the contained roles. The actual roles aren't returned only the translations.
     * The array looks like: (RoleSetName => (Role1Translation, Rol2Translation..), ...)
     * 
     * @return json response
     * @throws AccessDeniedException
     */
    public function getTranslatedRoleSetsAction() {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_ROLE_SET')) {
            throw new AccessDeniedException('user.role.no_right_to_view_page');
        }

        $translatedRoleSets = $this->get('core_user.role_manager')->getTranslatedRoleSetsAction();

        $view = $this->view($translatedRoleSets);
        return $this->handleView($view);
    }

    /**
     * Returns the appropriate formatted json for IVHTreeView containing every role, except ROLE_USER, ROLE_ADMIN and ROLE_SUPER_ADMIN. 
     * 
     * @return json response
     * @throws BadRequestException
     * @throws AccessDeniedException
     */
    public function getFullRoleTreeAction() {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_ROLE_SET')) {
            throw new AccessDeniedException('user.role.no_right_to_view_page');
        }

        $roleManager = $this->get('core_user.role_manager');

        $RoleAdminRoleSuperAdminAndRoleUserRoles = $roleManager->getUserRoles($this->getParameter('security.role_hierarchy.roles'));
        $RoleAdminRoleSuperAdminAndRoleUserRoles[] = 'ROLE_ADMIN';
        $RoleAdminRoleSuperAdminAndRoleUserRoles[] = 'ROLE_SUPER_ADMIN';

        $roleHierarchy = $roleManager->removeRolesFromHierarchy($RoleAdminRoleSuperAdminAndRoleUserRoles);
        $roleTree = $roleManager->getDeepRoleHierarchy(null, $roleHierarchy);

        $view = $this->view($this->get('core_user.role_handling_front_end_adapter')->getRoleTreeForFrontEnd($roleTree));
        return $this->handleView($view);
    }

    /**
     * Removes the RoleSet of the given roleSetId.
     * 
     * @param integer $roleSetId
     * @return json response
     * @throws AccessDeniedException
     * @throws NotFoundEntityException
     */
    public function removeRoleSetAction($roleSetId) {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_EDIT_ROLE_SET')) {
            throw new AccessDeniedException('user.role.no_right_to_edit');
        }

        $roleSet = $this->get('core_user.role_manager')->removeRoleSetAction($roleSetId);

        $view = $this->view($roleSet, Response::HTTP_NO_CONTENT);

        return $this->handleView($view);
    }

    /**
     * Creates a new RoleSet if its unique. The roleSet will contain only the highest roles in the role chain, means that
     * if it contains e.g. ROLE_USER, ROLE_ADMIN and ROLE_SUPER_ADMIN only ROLE_SUPER_ADMIN will  be persisted. Of course it 
     * applies for every individual chain. The uniqueness is checked against the RoleSet names and also the contained roles.
     * 
     * @param Request $request
     * @return json response
     * @throws InvalidFormException
     * @throws AccessDeniedException
     */
    public function createRoleSetFormAction(Request $request) {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_EDIT_ROLE_SET')) {
            throw new AccessDeniedException('user.role.no_right_to_edit');
        }

        $roleSet = $this->get('core_user.role_manager')->createRoleSet($request->request->all()[$this->get('core_user.form.type.role_set')->getName()]);

        $view = $this->view($roleSet, Response::HTTP_NO_CONTENT);

        return $this->handleView($view);
    }

    /**
     * Returns the appropriate formatted json for IVHTreeView for the RoleSet of the given roleSetId containing every role in the role chain.
     * Thus if the RoleSet contains a role every subroles will be returned as well.
     * 
     * @param integer $roleSetId
     * @return json response
     * @throws AccessDeniedException
     * @throws NotFoundEntityException
     * @throws BadRequestException
     */
    public function getDetailedRoleSetTreeAction($roleSetId) {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_ROLE_SET')) {
            throw new AccessDeniedException('user.role.no_right_to_view_page');
        }

        $roleManager = $this->get('core_user.role_manager');

        $roleSet = $roleManager->getRoleSetOr404($roleSetId);

        $RoleAdminRoleSuperAdminAndRoleUserRoles = $this->get('core_user.role_manager')->getUserRoles($this->getParameter('security.role_hierarchy.roles'));
        $RoleAdminRoleSuperAdminAndRoleUserRoles[] = 'ROLE_ADMIN';
        $RoleAdminRoleSuperAdminAndRoleUserRoles[] = 'ROLE_SUPER_ADMIN';
        $sanitizedRoleHierarchy = $roleManager->removeRolesFromHierarchy($RoleAdminRoleSuperAdminAndRoleUserRoles);

        $rolesWithChildren = $roleManager->getDeepRoleHierarchy($roleManager->replaceRolesOfSuperadminIfRemovedFromHierarchy($roleSet->getRoles(), $sanitizedRoleHierarchy), $sanitizedRoleHierarchy);

        $view = $this->view($this->get('core_user.role_handling_front_end_adapter')->getRoleTreeForFrontEnd($rolesWithChildren), Response::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Returns all the RoleSets with every role they grant, thus not only the roles they contain, but the children of these.
     * ROLE_ADMIN and ROLE_SUPER_ADMIN are filtered out thus if the set contains one of them then returns only the children of these.
     * The translation of the roles are also wrapped, so the returned array will looks like:
     * (RoleSetName1 => (RoleXTranslation => ROLE_X,...), RoleSetName2...)
     * 
     * @return json response
     * @throws AccessDeniedException
     * @throws BadRequestException
     */
    public function getUndeletedDetailedRoleSetsAction() {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_ROLE_SET')) {
            throw new AccessDeniedException('user.role.no_right_to_view_page');
        }

        $roleManager = $this->get('core_user.role_manager');

        $roleAdminRoleSuperAdminAndRoleUserRoles = $roleManager->getUserRoles($this->getParameter('security.role_hierarchy.roles'));
        $roleAdminRoleSuperAdminAndRoleUserRoles[] = 'ROLE_ADMIN';
        $roleAdminRoleSuperAdminAndRoleUserRoles[] = 'ROLE_SUPER_ADMIN';
        $sanitizedRoleHierarchy = $roleManager->removeRolesFromHierarchy($roleAdminRoleSuperAdminAndRoleUserRoles);

        $detailedRoleSets = $roleManager->getUndeletedDetailedRoleSets($sanitizedRoleHierarchy);

        $view = $this->view($detailedRoleSets, Response::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Edits a RoleSet if its exists with the given roleSetId. If it isnt exists then returns exception. Validates that the name and the set of roles
     * must be unique (excluding itself). Like in the creation process, child roles will be filtered, only the highest role will remain in the chain.
     * 
     * @param Request $request
     * @param integer $roleSetId
     * @return json response
     * @throws InvalidFormException
     * @throws NotFoundEntityException
     * @throws AccessDeniedException
     */
    public function editRoleSetFormAction(Request $request, $roleSetId) {
        if (false === $this->isGranted('ROLE_ADMIN_CAN_EDIT_ROLE_SET')) {
            throw new AccessDeniedException('user.role.no_right_to_edit');
        }

        $roleSet = $this->get('core_user.role_manager')->editRoleSet($request->request->all()[$this->get('core_user.form.type.role_set')->getName()], $roleSetId);

        $view = $this->view($roleSet, Response::HTTP_NO_CONTENT);

        return $this->handleView($view);
    }

}
