<?php

namespace Core\UserBundle\Form\DataTransformer;

use Core\UserBundle\Managers\RoleManager;
use Symfony\Component\Form\DataTransformerInterface;
use Core\UserBundle\Entity\RoleSet;

class RolesToFilteredRolesTransformer implements DataTransformerInterface {

    private $roleManager;
    private $roleHierarchy;

    public function __construct(RoleManager $roleManager, $roleHierarchy) {
        $this->roleManager = $roleManager;
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * Transforms an object (RoleSet) to an empty array.
     * 
     * @param  RoleSet|null $roleSet
     * @return array
     */
    public function transform($roleSet) {
        //Its not needed to transform into anything as we display the roles outside the form with a plugin.
        return array();
    }

    /**
     * Filters the selected roles. Fake and child roles with it's parent included too will be removed.
     *
     * @param  array $arrayOfRoles
     * @return array
     */
    public function reverseTransform($arrayOfRoles) {
        if (is_null($arrayOfRoles)) {
            return null;
        }

        return $this->roleManager->filterRoles($arrayOfRoles, $this->roleHierarchy);
    }

}
