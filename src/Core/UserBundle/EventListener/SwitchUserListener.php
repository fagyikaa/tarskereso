<?php

namespace Core\UserBundle\EventListener;

use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Core\UserBundle\Managers\RoleManager;
use Core\CommonBundle\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class SwitchUserListener {

    protected $roleManager;
    protected $translator;

    public function __construct(RoleManager $roleManager, TranslatorInterface $translator) {
        $this->roleManager = $roleManager;
        $this->translator = $translator;
    }

    /**
     * If the impersonation target user is an admin and the action is not backward to admin
     * from a current impersonation then throws exception.
     * 
     * @param SwitchUserEvent $event
     * @throws AccessDeniedException
     */
    public function onSwitchUser(SwitchUserEvent $event) {
        if (true === $this->roleManager->isAdmin($event->getTargetUser()) && '_exit' !== $event->getRequest()->query->get('_switch_user')) {
            throw new AccessDeniedException('user.impersonating_an_admin');
        }
    }

}
