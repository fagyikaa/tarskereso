<?php

namespace Core\UserBundle\Twig;

use Core\UserBundle\Entity\User;
use Core\UserBundle\Managers\RoleManager;

class IsAdminExtension extends \Twig_Extension {

    protected $roleManager;
    
    public function __construct(RoleManager $roleManager) {
        $this->roleManager = $roleManager;
    }
    
    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('is_admin', array($this, 'isAdminFilter')),
        );
    }

    public function isAdminFilter(User $user) {
        return $this->roleManager->isAdmin($user);
    }

    public function getName() {
        return 'is_admin';
    }

}
