<?php

namespace Core\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManagerInterface;
use Core\UserBundle\Entity\RoleSet;

class UniqueRoleSetValidator extends ConstraintValidator {

    protected $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /**
     * This method checks if any other role set has the same name or contains the same roles.
     * 
     * @param RoleSet $roleSet
     * @param Constraint $constraint
     */
    public function validate($roleSet, Constraint $constraint) {
        if (is_null($roleSet)) {
            return;
        }
        $repo = $this->em->getRepository('CoreUserBundle:RoleSet');

        $roleSetOrNullForName = $repo->findOneBy(array('name' => $roleSet->getName()));
        if ($roleSetOrNullForName instanceof RoleSet && $roleSetOrNullForName !== $roleSet) {
            $this->context->buildViolation('user.role_set.name.unique')
                    ->atPath('name')
                    ->addViolation();
        }

        if (!$this->isSetOfRolesUnique($roleSet, $repo)) {
            $this->context->buildViolation('user.role_set.roles.unique')
                    ->atPath('roles')
                    ->addViolation();
        }
    }

    /**
     * Checks if the given role set's roles are the same as any other role set's. The ordering does not
     * count.
     * 
     * @param RoleSet $roleSet
     * @param EntityManager $repo
     * @return boolean
     */
    private function isSetOfRolesUnique(RoleSet $roleSet, $repo) {
        $roleSets = $repo->findAllUndeleted();
        $roles = $roleSet->getRoles();

        $key = array_search($roleSet, $roleSets);
        if ($key !== false) {
            unset($roleSets[$key]);
        }

        foreach ($roleSets as $roleSetFromDatabase) {
            $isUnique = false;
            $rolesOfRoleSet = $roleSetFromDatabase->getRoles();
            if (count($roles) === count($rolesOfRoleSet)) {
                foreach ($roles as $role) {
                    if (!in_array($role, $rolesOfRoleSet)) {
                        $isUnique = true;
                    }
                }
            } else {
                $isUnique = true;
            }
            if (!$isUnique) {
                return false;
            }
        }
        return true;
    }

}
