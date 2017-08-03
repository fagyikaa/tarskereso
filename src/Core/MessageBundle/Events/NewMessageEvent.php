<?php

namespace Core\MessageBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Core\MessageBundle\Entity\Message;

class NewMessageEvent extends Event {

    protected $message;

    public function __construct(Message $message) {
        $this->message = $message;
    }

    /**
     * Gets message
     * 
     * @return Message
     */
    public function getMessage() {
        return $this->message;
    }

}

