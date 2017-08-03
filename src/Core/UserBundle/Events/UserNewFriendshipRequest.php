<?php

namespace Core\UserBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Core\UserBundle\Entity\UserFriendship;

class UserNewFriendshipRequest extends Event {

    protected $friendship;

    public function __construct(UserFriendship $friendship) {
        $this->friendship = $friendship;
    }

    /**
     * Gets friendship
     * 
     * @return UserFriendship
     */
    public function getFriendship() {
        return $this->friendship;
    }

}
