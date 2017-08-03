<?php

namespace Core\UserBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Core\UserBundle\Events\UserBundleEvents;
use Core\UserBundle\Events\UserAdminRoleUpdateEvent;
use Core\UserBundle\Managers\FriendshipManager;

class UserBundleEventsSubscriber implements EventSubscriberInterface {

    protected $friendshipManager;
    
    public function __construct(FriendshipManager $friendshipManager) {
        $this->friendshipManager = $friendshipManager;
    }

    public static function getSubscribedEvents() {
        return array(
            UserBundleEvents::USER_ADMIN_ROLE_UPDATE => 'onUserAdminRoleUpdate',
        );
    }

    /**
     * Calls the removeEveryBlockingFromUser() function of FriendshipManager with
     * the user in $event and false.
     * 
     * @param UserAdminRoleUpdateEvent $event
     */
    public function onUserAdminRoleUpdate(UserAdminRoleUpdateEvent $event) {
        $this->friendshipManager->removeEveryBlockingFromUser($event->getUser(), false);
    }    

}
