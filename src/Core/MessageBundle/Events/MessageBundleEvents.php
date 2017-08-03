<?php

namespace Core\MessageBundle\Events;

/**
 * Contains all events thrown in the ProfileHelper
 */
final class MessageBundleEvents {

    /*
     * MESSAGE_NEW_MESSAGE event occurs when a user sends a new message to an other user.
     */
    const MESSAGE_NEW_MESSAGE = 'core_message.new.message';
}
