<?php

namespace Core\CommonBundle\Twig;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\SwitchUserRole;

class OriginalUserIdExtension extends \Twig_Extension {

    protected $tokenStorage;
    protected $authorizationChecker;

    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * The function's name: get_original_user_id
     * 
     * @return array
     */
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('get_original_user_id', array($this, 'getOriginalUserId')),
        );
    }

    /**
     * If an admin is impersonating a user this function returns the id of the admin's original
     * user object, null otherwise.
     * 
     * @return int|null
     */
    public function getOriginalUserId() {
        if (false === is_null($this->tokenStorage->getToken()) && $this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            foreach ($this->tokenStorage->getToken()->getRoles() as $role) {
                if ($role instanceof SwitchUserRole && false === is_null($role->getSource()->getUser())) {
                    return $role->getSource()->getUser()->getId();
                }
            }
        }

        return null;
    }

    public function getName() {
        return 'original_user_id_extension';
    }

}
