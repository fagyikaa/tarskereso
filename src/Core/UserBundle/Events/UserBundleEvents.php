<?php

namespace Core\UserBundle\Events;

/**
 * Contains all events thrown in the ProfileHelper
 */
final class UserBundleEvents {

    /*
     * USER_ADMIN_ROLE_UPDATE event occurs when a user became an admin or an admin's role
     * has been edited.
     */
    const USER_ADMIN_ROLE_UPDATE = 'core_user.role_change.new_admin';
    
    /*
     * USER_NEW_FRIENDSHIP_REQUEST event occurs when a user requests friendship from an other.
     */
    const USER_NEW_FRIENDSHIP_REQUEST = 'core_user.friendship.new_request';
}
