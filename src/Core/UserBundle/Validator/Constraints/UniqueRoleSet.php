<?php

namespace Core\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueRoleSet extends Constraint {

    public function validatedBy() {
        return 'unique_role_set';
    }

    public function getTargets() {
        return self::CLASS_CONSTRAINT;
    }

}
