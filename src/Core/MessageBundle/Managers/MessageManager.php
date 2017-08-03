<?php

namespace Core\MessageBundle\Managers;

use Doctrine\ORM\EntityManagerInterface;
use Core\UserBundle\Entity\User;
use Symfony\Component\Translation\TranslatorInterface;
use Core\MessageBundle\Entity\Message;
use Core\MessageBundle\Managers\ConversationManager;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Core\MessageBundle\Events\MessageBundleEvents;
use Core\MessageBundle\Events\NewMessageEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Core\CommonBundle\Exception\EntityValidationException;

class MessageManager {

    protected $em;
    protected $translator;
    protected $conversationManager;
    protected $validator;
    protected $dispatcher;
    
    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator, ConversationManager $conversationManager, ValidatorInterface $validator, EventDispatcherInterface $dispatcher) {
        $this->em = $em;
        $this->translator = $translator;
        $this->conversationManager = $conversationManager;
        $this->validator = $validator;
        $this->dispatcher = $dispatcher;
    }
     
    /**
     * If the two users haven't started a conversation yet then creates a Conversation. Creates
     * and validates a Message with $text and $author. If validation fails exception is thrown.
     * 
     * @param User $author
     * @param User $reciever
     * @param string $text
     * @return Message
     * @throws EntityValidationException
     */
    public function createMessageForUsers(User $author, User $reciever, $text) {
        $conversation = $this->conversationManager->getOrCreateConversationForUsers($author, $reciever, null, null);
        $message = new Message();
        $message->setAuthor($author);
        $message->setText($text);        
       
        $violationList = $this->validator->validate($message);       
        if ($violationList->count() > 0) {
            throw new EntityValidationException($violationList);
        }
        
        $conversation->addMessage($message);
        $message->setConversation($conversation);
        $this->em->persist($message);
        $this->em->flush();
        
        $this->dispatcher->dispatch(MessageBundleEvents::MESSAGE_NEW_MESSAGE, new NewMessageEvent($message));
        
        return $message;
    }
}
