<?php

namespace Core\UserBundle\Managers;

use Core\CommonBundle\Helper\CommonHelper;
use Symfony\Component\Translation\TranslatorInterface;
use Core\UserBundle\Entity\User;
use Core\UserBundle\Entity\RoleSet;
use Doctrine\ORM\EntityManagerInterface;
use Core\UserBundle\Managers\UserManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Core\UserBundle\Events\UserAdminRoleUpdateEvent;
use Core\UserBundle\Events\UserBundleEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Core\CommonBundle\Exception\NotFoundEntityException;
use Core\CommonBundle\Exception\InvalidFormException;
use Core\CommonBundle\Exception\BadRequestException;

class RoleManager {

    protected $roleHierArchy;
    protected $commonHelper;
    protected $translator;
    protected $em;
    protected $userManager;
    protected $dispatcher;
    protected $formFactory;
    protected $container;

    public function __construct($roleHierArchy, CommonHelper $commonHelper, TranslatorInterface $translator, EntityManagerInterface $em, UserManager $userManager, EventDispatcherInterface $dispatcher, FormFactoryInterface $formFactory, $container) {
        $this->roleHierArchy = $roleHierArchy;
        $this->commonHelper = $commonHelper;
        $this->translator = $translator;
        $this->em = $em;
        $this->userManager = $userManager;
        $this->dispatcher = $dispatcher;
        $this->formFactory = $formFactory;
        $this->container = $container;
    }

    /**
     * Returns the RoleSet with $roleSetId or throws exception if no RoleSet exists with
     * this id or $filterDeleted is true and the RoleSet is deleted.
     * 
     * @param integer $roleSetId
     * @param boolean $filterDeleted
     * @return RoleSet
     * @throws NotFoundEntityException
     */
    public function getRoleSetOr404($roleSetId, $filterDeleted = false) {
        $roleSet = $this->em->getRepository('CoreUserBundle:RoleSet')->find($roleSetId);

        if (false === ($roleSet instanceof RoleSet) || $filterDeleted && $roleSet->isDeleted()) {
            throw new NotFoundEntityException('');
        }

        return $roleSet;
    }

    /**
     * Returns every RoleSets or $filterDeleted then only the undeleted ones.
     * 
     * @param boolean $filterDeleted
     * @return array
     */
    public function getRoleSets($filterDeleted = false) {
        if ($filterDeleted) {
            return $this->em->getRepository('CoreUserBundle:RoleSet')->findBy(array('deletedAt' => null));
        }

        return $this->em->getRepository('CoreUserBundle:RoleSet')->findAll();
    }

    /**
     * Checks if the given user object has ROLE_ADMIN role.
     * 
     * @param User $user
     * @return boolean
     * @throws BadRequestException
     */
    public function isAdmin(User $user, array $roleHierarchy = null) {
        if (is_null($roleHierarchy)) {
            $roleHierarchy = $this->roleHierArchy;
        }
        $this->throwExceptionIfNotValidHierarchy($roleHierarchy);

        return $this->hasRole($user, 'ROLE_ADMIN', $roleHierarchy);
    }

    /**
     * Checks if the given user object has $role role.
     * 
     * @param User $user
     * @param String $role
     * @return boolean
     * @throws BadRequestException
     */
    public function hasRole(User $user, $role, array $roleHierarchy = null) {
        if (is_null($roleHierarchy)) {
            $roleHierarchy = $this->roleHierArchy;
        }
        $this->throwExceptionIfNotValidHierarchy($roleHierarchy);

        foreach ($user->getRoles() as $userRole) {
            if ($userRole === $role || $this->isSubRole($userRole, $role, $roleHierarchy)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns in a flat array every roles which are in the given $roleHierarchy.
     * 
     * @param array $roleHierarchy
     * @return array
     * @throws BadRequestException
     */
    public function getEveryRoleOfHierarchyForForm(array $roleHierarchy = null) {
        if (is_null($roleHierarchy)) {
            $roleHierarchy = $this->roleHierArchy;
        }
        $this->throwExceptionIfNotValidHierarchy($roleHierarchy);

        $roles = array();
        foreach ($roleHierarchy as $role => $subrolesArray) {
            $roles[$role] = $role;
            foreach ($subrolesArray as $subrole) {
                $roles[$subrole] = $subrole;
            }
        }

        return $roles;
    }

    /**
     * Returns an array containing every role in $roles and it's every children roles (including the children of children
     * and so on recursively).
     * 
     * @param array $roles
     * @param array $roleHierarchy
     * @return array
     * @throws BadRequestException
     */
    public function getEveryRolesAndSubrolesOfGivenRolesInHierarchy(array $roles, array $roleHierarchy = null) {
        if (is_null($roleHierarchy)) {
            $roleHierarchy = $this->roleHierArchy;
        }
        $this->throwExceptionIfNotValidHierarchy($roleHierarchy);

        $branches = array();
        foreach ($roles as $role) {
            $branches[$role] = $this->getChildrenBranch($role, $roleHierarchy);
        }

        $plainRolesArray = array();
        $it = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($branches), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($it as $role => $childRoles) {
            $plainRolesArray[] = $role;
        }

        return array_values(array_unique($plainRolesArray));
    }

    /**
     * Builds a deep hierarchy from the given $roleHierarchy or the default one in security.yml. The given $roleHierarchy must be maximum 
     * one dimensional associative array like the role hierarchy in the security.yml. If $roots is null then the default roots of the $roleHierarchy
     * (given or from the security.yml) will be used. From this flat hierarchy it cunstructs a deep hierarchy where the array keys are the
     * roles and the leaf roles (which are not apperas as parents in the flat hierarchy) are arrays with the value of an empty array.
     * E.g. if the role hierarchy is like:
     * 
     * ROLE1: LEAF
     * ROOT1: ROLE1
     * ROLE2.1: LEAF
     * ROLE2.2 [ROLE2.3, ROLE2.4]
     * ROLE2.3 ROLE2.5
     * ROLE2.5 LEAF
     * ROLE2.4 LEAF
     * ROOT2: [ROLE2.1, ROLE2.2]
     * 
     * then the following array returned:
     * 
     * [
     *      ROOT1 => [
     *          ROLE1 [
     *              LEAF => []
     *          ]
     *      ],
     *      ROOT2 => [
     *          ROLE2.1 => [
     *              LEAF => []
     *          ],
     *          ROLE2.2 => [
     *              ROLE2.3 => [
     *                  ROLE2.5 => [
     *                      LEAF => []   
     *                  ]
     *              ],
     *              ROLE2.4 => [
     *                  LEAF => []
     *              ]
     *          ]    
     *      ]
     * ]
     * 
     * @param array|null $roots
     * @param array|null $roleHierarchy
     * @return array
     * @throws BadRequestException
     */
    public function getDeepRoleHierarchy(array $roots = null, array $roleHierarchy = null) {
        if (is_null($roleHierarchy)) {
            $roleHierarchy = $this->roleHierArchy;
        }
        $this->throwExceptionIfNotValidHierarchy($roleHierarchy);

        if (is_null($roots)) {
            $roots = $this->getRootsOfDeppRoleHierarchy($roleHierarchy);
        }

        $deepRoleHierarchy = array();
        foreach ($roots as $rootRole) {
            $deepRoleHierarchy[$rootRole] = $this->getChildrenBranch($rootRole, $roleHierarchy);
        }

        return $deepRoleHierarchy;
    }

    /**
     * Recursively search for the children roles of the given $rootRole in the $roleHierarchy. The $roleHierarchy has to be a flat 
     * hierarchy as like the role hierarchy in security.yml. Every role apperas as a key of an associative array. If the value of a key
     * is a non-empty array then the keys of that array are the direct children of the role. If the value of a key is an empty array 
     * then that key doesn't have any child, it's a leaf role in the given $roleHierarchy.
     * E.g. if the role hierarchy is like:
     * 
     * ROLE1: LEAF
     * ROOT1: ROLE1
     * ROLE2.1: LEAF
     * ROLE2.2 [ROLE2.3, ROLE2.4]
     * ROLE2.3 ROLE2.5
     * ROLE2.5 LEAF
     * ROLE2.4 LEAF
     * ROOT2: [ROLE2.1, ROLE2.2]
     * 
     * then the following array returned for ROOT2:
     * 
     *      [
     *          ROLE2.1 => [
     *              LEAF => ''
     *          ],
     *          ROLE2.2 => [
     *              ROLE2.3 => [
     *                  ROLE2.5 => [
     *                      LEAF => []   
     *                  ]
     *              ],
     *              ROLE2.4 => [
     *                  LEAF => []
     *              ]
     *          ]    
     *      ]
     * 
     * @param string $rootRole
     * @param array $roleHierarchy
     * @return array
     * @throws BadRequestException
     */
    public function getChildrenBranch($rootRole, array $roleHierarchy = null) {
        if (is_null($roleHierarchy)) {
            $roleHierarchy = $this->roleHierArchy;
        }
        $this->throwExceptionIfNotValidHierarchy($roleHierarchy);

        $branch = array();
        if (array_key_exists($rootRole, $roleHierarchy)) {
            foreach ($roleHierarchy[$rootRole] as $subrole) {
                if (array_key_exists($subrole, $roleHierarchy)) {
                    $branch[$subrole] = $this->getChildrenBranch($subrole, $roleHierarchy);
                } else {
                    $branch[$subrole] = array();
                }
            }
        }

        return $branch;
    }

    /**
     * Firstly filters out those roles from $roles which are fake(can not be found in the hierarchy). Then filters out those roles
     * which parent (not necessary the immediate) is in the $roles as well. Finally checks if in the $roleHierarchy 
     * ROLE_SUPER_ADMIN exists and contains the same roles as the filtered ones and if yes, replace them with ROLE_SUPER_ADMIN.
     * 
     * @param array $roles
     * @param array $roleHierarchy
     * @return array
     * @throws BadRequestException
     */
    public function filterRoles(array $roles, array $roleHierarchy = null) {
        if (is_null($roleHierarchy)) {
            $roleHierarchy = $this->roleHierArchy;
        }
        $this->throwExceptionIfNotValidHierarchy($roleHierarchy);

        $rolesWithoutFakes = $this->filterFakeRoles($roles, $roleHierarchy);
        $rolesWithoutFakesAndChildren = $this->filterChildRoles($rolesWithoutFakes, $roleHierarchy);
        $roleSuperAdminOrFalse = $this->replaceRolesIfSameAsRoleSuperAdmin($rolesWithoutFakesAndChildren, $roleHierarchy);

        return $roleSuperAdminOrFalse === false ? $rolesWithoutFakesAndChildren : $roleSuperAdminOrFalse;
    }

    /**
     * Removes the given $roleToRemove from the given $roleHierarchy. If the $roleToRemove has subroles which isn't exists
     * as a main role in the $roleHierarchy then adds them to the $roleHierarchy as a main role but the value of these are empty arrays.
     * If $roleToRemove appears somewhere as a subrole then replaces it with the subroles of $roleToRemove or simply deletes if it doesn't have subroles.
     * The values of those roles which has only $roleToRemove as subrole will be empty array as well.
     * 
     * @param string $roleToRemove
     * @param array $roleHierarchy
     * @return array
     * @throws BadRequestException
     */
    public function removeRoleFromHierarchy($roleToRemove, array $roleHierarchy = null) {
        if (is_null($roleHierarchy)) {
            $roleHierarchy = $this->roleHierArchy;
        }
        $this->throwExceptionIfNotValidHierarchy($roleHierarchy);
        //If $roleToRemove is a main role in the hierarchy then add the children of it as main role to the hierarchy
        if (array_key_exists($roleToRemove, $roleHierarchy)) {
            $subRolesOfRoleToRemove = $roleHierarchy[$roleToRemove];
            unset($roleHierarchy[$roleToRemove]);
            foreach ($subRolesOfRoleToRemove as $subrole) {
                if (!array_key_exists($subrole, $roleHierarchy)) {
                    $roleHierarchy[$subrole] = array();
                }
            }
        }

        //If $roleToRemove is a child role somewhere then replace it with it's children
        foreach ($roleHierarchy as &$subRoles) {
            if (in_array($roleToRemove, $subRoles)) {
                unset($subRoles[array_search($roleToRemove, $subRoles)]);
                if (isset($subRolesOfRoleToRemove)) {
                    $subRoles = array_merge($subRoles, $subRolesOfRoleToRemove);
                }
            }
        }

        return $roleHierarchy;
    }

    /**
     * Removes the given $roles from the given $roleHierarchy. If any role of the $roles has subroles which isn't exists
     * as a main role in the $roleHierarchy then adds them to the $roleHierarchy as a main role but the value of these are empty arrays.
     * If any role of $roles appears somewhere as a subrole then replaces it with the subroles of that role or simply deletes if it doesn't have subroles.
     * The values of those roles which has only the role to delete as subrole will has empty array as well.
     * 
     * @param array $roles
     * @param array $roleHierarchy
     * @return array
     * @throws BadRequestException
     */
    public function removeRolesFromHierarchy(array $roles, array $roleHierarchy = null) {
        if (is_null($roleHierarchy)) {
            $roleHierarchy = $this->roleHierArchy;
        }
        $this->throwExceptionIfNotValidHierarchy($roleHierarchy);

        foreach ($roles as $role) {
            $roleHierarchy = $this->removeRoleFromHierarchy($role, $roleHierarchy);
        }

        return $roleHierarchy;
    }

    /**
     * Returns every roles which start with ROLE_USER. Every user role starts with ROLE_USER and
     * every admin role starts with ROLE_ADMIN thus the returned roles are the simple user roles.
     * 
     * @param array $roleHierarchy
     * @return array
     * @throws BadRequestException
     */
    public function getUserRoles(array $roleHierarchy = null) {
        if (is_null($roleHierarchy)) {
            $roleHierarchy = $this->roleHierArchy;
        }
        $this->throwExceptionIfNotValidHierarchy($roleHierarchy);

        $userRoles = array();
        foreach ($this->getEveryRolesInPlainArray($roleHierarchy) as $role) {
            if (strpos($role, 'ROLE_USER') !== false) {
                $userRoles[] = $role;
            }
        }

        return $userRoles;
    }

    /**
     * Returns every role in the given $roleHierarchy in a plain flat array.
     * 
     * @param array $roleHierarchy
     * @return array
     * @throws BadRequestException
     */
    public function getEveryRolesInPlainArray(array $roleHierarchy = null) {
        if (is_null($roleHierarchy)) {
            $roleHierarchy = $this->roleHierArchy;
        }
        $this->throwExceptionIfNotValidHierarchy($roleHierarchy);

        $roles = array();
        foreach ($roleHierarchy as $role => $subrolesArray) {
            $roles[] = $role;
            foreach ($subrolesArray as $subrole) {
                $roles[] = $subrole;
            }
        }

        return array_values(array_unique($roles));
    }

    /**
     * Returns every RoleSets which deletedAt property is null. If no such RoleSet
     * exists then returns empty array.
     * 
     * @param array $roleHierarchy
     * @return array
     */
    public function getUndeletedRoleSets() {
        return $this->em->getRepository('CoreUserBundle:RoleSet')->findAllUndeleted();
    }

    /**
     * Returns an array with keys as the name of undeleted RoleSets containing an array
     * with the apropriate RoleSet's id, and every role's translation as key and the role
     * itself as value.
     * 
     * @return array
     */
    public function getRoleSetsWithTranslation() {
        $roleSets = $this->getRoleSets(true);

        $roleSetsWithTranslation = array();
        foreach ($roleSets as $roleSet) {
            $plainRolesArray = array();
            $plainRolesArray['id'] = $roleSet->getId();

            foreach ($roleSet->getRoles() as $role) {
                $plainRolesArray[$this->translator->trans('role.' . $role, array(), 'role')] = $role;
            }

            $roleSetsWithTranslation[$roleSet->getName()] = $plainRolesArray;
        }

        return $roleSetsWithTranslation;
    }

    /**
     * Sets $user's roles to the filtered roles of $roles. Filtering is done by
     * filterRoles() function. If the User will be an admin after the changing then
     * removes every blocking off of them.
     * 
     * @param User $user
     * @param array $roles
     */
    public function updateUserRoles(User $user, array $roles) {
        if (count($roles) === 0) {
            $user->setRoles($roles);
        } else {
            $user->setRoles($this->filterRoles($roles));

            if ($this->isAdmin($user)) {
                $this->dispatcher->dispatch(UserBundleEvents::USER_ADMIN_ROLE_UPDATE, new UserAdminRoleUpdateEvent($user));
            }
        }

        $this->userManager->updateUser($user);
    }

    /**
     * Return all of the RoleSets including the translations of the contained roles. The actual roles aren't returned only the translations.
     * The array looks like: (RoleSetName => (Role1Translation, Rol2Translation..), ...)
     * 
     * @return array
     */
    public function getTranslatedRoleSetsAction() {
        $translatedRoleSets = array();
        foreach ($this->getRoleSets() as $roleSet) {

            $tempArrayForRoleTranslations = array();
            foreach ($roleSet->getRoles() as $role) {
                $tempArrayForRoleTranslations[] = $this->translator->trans('role.' . $role, array(), 'role');
            }

            $translatedRoleSets[] = array('name' => $roleSet->getName(), 'roles' => $tempArrayForRoleTranslations, 'id' => $roleSet->getId(), 'deletedAt' => $roleSet->getDeletedAt());
        }

        return $translatedRoleSets;
    }

    /**
     * Removes the RoleSet of the given roleSetId.
     * 
     * @param integer $roleSetId
     * @return RoleSet
     * @throws NotFoundEntityException
     */
    public function removeRoleSetAction($roleSetId) {
        $roleSet = $this->getRoleSetOr404($roleSetId, true);

        $roleSet->setDeletedAt(new \DateTime());
        $this->em->persist($roleSet);
        $this->em->flush();

        return $roleSet;
    }

    /**
     * Creates and saves a RoleSet from $parameters. In case of validation fails
     * throws exception.
     * 
     * @param array $parameters
     * @return RoleSet
     * @throws InvalidFormException
     */
    public function createRoleSet(array $parameters) {
        $form = $this->formFactory->create($this->container->get('core_user.form.type.role_set'));

        $form->submit($parameters);
        if ($form->isValid()) {
            $roleSet = $form->getData();
            $this->em->persist($roleSet);
            $this->em->flush();

            return $roleSet;
        }

        throw new InvalidFormException($form->getErrors(true));
    }

    /**
     * Edits a RoleSet if its exists with the given roleSetId. If it isnt exists then returns exception. Validates that the name and the set of roles
     * must be unique (excluding itself). Like in the creation process, child roles will be filtered, only the highest role will remain in the chain.
     * 
     * @param array $parameters
     * @param integer $roleSetId
     * @return RoleSet
     * @throws InvalidFormException
     * @throws NotFoundEntityException
     */
    public function editRoleSet(array $parameters, $roleSetId) {
        $roleSet = $this->getRoleSetOr404($roleSetId, true);

        $form = $this->formFactory->create($this->container->get('core_user.form.type.role_set'), $roleSet);

        $form->submit($parameters);
        if ($form->isValid()) {
            $roleSet = $form->getData();

            $this->em->persist($roleSet);
            $this->em->flush();
            return $roleSet;
        }

        throw new InvalidFormException($form->getErrors(true));
    }

    /**
     * Returns all the RoleSets with every role they grant, thus not only the roles they contain, but the children of these.
     * ROLE_ADMIN and ROLE_SUPER_ADMIN are filtered out thus if the set contains one of them then returns only the children of these.
     * The translation of the roles are also wrapped, so the returned array will looks like:
     * (RoleSetName1 => (RoleXTranslation => ROLE_X,...), RoleSetName2...) 
     * Throws exception if the $roleHierarchy is invalid.
     * 
     * @return array
     * @throws BadRequestException
     */
    public function getUndeletedDetailedRoleSets(array $roleHierarchy) {
        $this->throwExceptionIfNotValidHierarchy($roleHierarchy);

        $roleSets = $this->getUndeletedRoleSets();

        $detailedRoleSets = array();
        foreach ($roleSets as $roleSet) {
            $rolesWithChildren = $this->getEveryRolesAndSubrolesOfGivenRolesInHierarchy($this->replaceRolesOfSuperadminIfRemovedFromHierarchy($roleSet->getRoles(), $roleHierarchy), $roleHierarchy);

            $plainRolesArray = array();
            foreach ($rolesWithChildren as $role) {
                $plainRolesArray[$this->translator->trans('role.' . $role, array(), 'role')] = $role;
            }

            $detailedRoleSets[$roleSet->getName()] = $plainRolesArray;
        }

        return $detailedRoleSets;
    }

    /**
     * If $roles contains ROLE_SUPER_ADMIN but the $sanitizedRoleHierarchy doesn't contain such main role then
     * replaces ROLE_SUPER_ADMIN in $roles with the children of it in the base role hierarchy defined in security.yml.
     * 
     * @param array $roles
     * @param array $roleHierarchy
     * @return array
     * @throws BadRequestException
     */
    public function replaceRolesOfSuperadminIfRemovedFromHierarchy(array $roles, array $roleHierarchy) {
        $this->throwExceptionIfNotValidHierarchy($roleHierarchy);

        if (in_array('ROLE_SUPER_ADMIN', $roles) &&
                !array_key_exists('ROLE_SUPER_ADMIN', $roleHierarchy) &&
                array_key_exists('ROLE_SUPER_ADMIN', $this->roleHierArchy)) {
            unset($roles[array_search('ROLE_SUPER_ADMIN', $roles)]);
            $roles = array_merge($roles, $this->roleHierArchy['ROLE_SUPER_ADMIN']);
        }

        return array_values($roles);
    }

    /**
     * Removes those roles from the passed array which parent (according to the role hierarchy) is in the array as well.
     * If the remaining roles are the same which are in ROLE_ADMIN or ROLE_SUPER_ADMIN then one of these roles is returned.
     * 
     * @param array $roles
     * @param array $roleHierarchy
     * @return array
     */
    private function filterChildRoles($roles, $roleHierarchy) {
        $keysOfRolesToRemove = array();
        for ($i = 0; $i < count($roles); $i++) {
            $j = 0;
            while ($j < count($roles)) {
                if ($i !== $j && $this->isSubRole($roles[$i], $roles[$j], $roleHierarchy)) {
                    $keysOfRolesToRemove[] = $j;
                }
                $j++;
            }
        }

        foreach (array_unique($keysOfRolesToRemove) as $key) {
            unset($roles[$key]);
        }

        return array_values($roles);
    }

    /**
     * Removes those elements from the passed $roles array which are not contained in the role hierarchy (fake roles).
     * 
     * @param array $roles
     * @param array $roleHierarchy
     * @return array
     */
    private function filterFakeRoles($roles, $roleHierarchy) {
        foreach ($roles as $key => $role) {
            if (!array_key_exists($role, $roleHierarchy) && !$this->isAppearingAsSubrole($role, $roleHierarchy)) {
                unset($roles[$key]);
            }
        }

        return array_values($roles);
    }

    /**
     * Checks if the given $roleHierarchy can be a valid role hierarchy array and throws InvalidArgumentException
     * if due to the syntax of the array it can't be a valid role hierarchy.
     * 
     * @param array $roleHierarchy
     * @throws BadRequestException
     */
    private function throwExceptionIfNotValidHierarchy($roleHierarchy) {
        if ($this->commonHelper->getArrayDepth($roleHierarchy) > 2) {
            throw new BadRequestException('user.role.invalid_hierarchy');
        }

        foreach ($roleHierarchy as $role => $subrolesArray) {
            if (!is_string($role) || !is_array($subrolesArray)) {
                throw new BadRequestException('user.role.invalid_hierarchy');
            }
            foreach ($subrolesArray as $key => $subrole) {
                if (!is_int($key) || !is_string($subrole)) {
                    throw new BadRequestException('user.role.invalid_hierarchy');
                }
            }
        }
    }

    /**
     * Returns an array containing the root roles of the given $roleHierarchy.If a role in the hierarchy appears only as key 
     * and doesn't apperas as subrole then it's a root.
     * 
     * @param array $roleHierarchy
     * @return array
     */
    private function getRootsOfDeppRoleHierarchy($roleHierarchy) {
        $roots = array();
        foreach ($roleHierarchy as $role => $subRolesArray) {
            if (!$this->isAppearingAsSubrole($role, $roleHierarchy)) {
                $roots[] = $role;
            }
        }

        return $roots;
    }

    /**
     * Checks whether the given $role is only appears as a leaf role or as a parent role. The given $roleHierarchy must be
     * flat as like the role hierarchy in the security.yml thus if the $role is appearing in one of the $subRolesArray
     * returns true, false otherwise.
     * 
     * @param string $role
     * @param array $roleHierarchy
     * @return boolean
     */
    private function isAppearingAsSubrole($role, $roleHierarchy) {
        foreach ($roleHierarchy as $subRolesArray) {
            if (in_array($role, $subRolesArray)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the passed roles equals to the roles in ROLE_SUPER_ADMIN. If not then returns false,
     * if matches them then returns ROLE_SUPER_ADMIN.
     * 
     * @param string $roles
     * @param array $roleHierarchy
     * @return array|boolean
     */
    private function replaceRolesIfSameAsRoleSuperAdmin($roles, $roleHierarchy) {
        if (isset($roleHierarchy['ROLE_SUPER_ADMIN'])) {
            $superAdminRoles = $roleHierarchy['ROLE_SUPER_ADMIN'];
            $same = true;
            if (count($superAdminRoles) === count($roles)) {
                foreach ($roles as $role) {
                    if (!in_array($role, $superAdminRoles)) {
                        $same = false;
                    }
                }
                if ($same) {
                    return array('ROLE_SUPER_ADMIN');
                }
            }
        }

        return false;
    }

    /**
     * Checks wether $subrole is a child of $role in the $roleHierarchy.
     * 
     * @param string $role
     * @param string $subrole
     * @param array $roleHierarchy
     * @return boolean
     */
    private function isSubRole($role, $subrole, $roleHierarchy) {
        if (array_key_exists($role, $roleHierarchy) && in_array($subrole, $roleHierarchy[$role])) {
            return true;
        } elseif (array_key_exists($role, $roleHierarchy)) {
            foreach ($roleHierarchy[$role] as $childRole) {
                if ($this->isSubRole($childRole, $subrole, $roleHierarchy)) {
                    return true;
                }
            }
        }
        return false;
    }

}
