<?php

namespace Core\UserBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Core\UserBundle\Entity\User;

class UserAdminRoleUpdateEvent extends Event {

    protected $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    /**
     * Gets user
     * 
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

}

