<?php

namespace Core\MessageBundle\Managers;

use Doctrine\ORM\EntityManagerInterface;
use Core\UserBundle\Entity\User;
use Core\CommonBundle\Exception\NotFoundEntityException;
use Symfony\Component\Translation\TranslatorInterface;
use Core\MessageBundle\Entity\Conversation;

class ConversationManager {

    protected $em;
    protected $translator;

    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator) {
        $this->em = $em;
        $this->translator = $translator;
    }

    /**
     * Searches for a conversation where the two participants are $user1 and $user2. 
     * If they haven't started a conversation yet, then creates one where $user1 will be
     * the starter and $user2 will be the reciever. The already existing conversation will
     * contain (maximum) $length messages shifted by $offset or if both are null then every messages. 
     * 
     * @param User $user1
     * @param User $user2
     * @param integer|null $offset 
     * @param integer|null $length
     */
    public function getOrCreateConversationForUsers(User $user1, User $user2, $offset, $length) {
        $conversationOrNull = $this->em->getRepository('CoreMessageBundle:Conversation')->findConversationOfUsers($user1->getId(), $user2->getId());

        if (is_null($conversationOrNull)) {
            $conversationOrNull = new Conversation();
            $conversationOrNull->setStarter($user1);
            $conversationOrNull->setReciever($user2);
            $this->em->persist($conversationOrNull);
        } else {
            if (is_int($offset)) {
                $convertedOffset = $conversationOrNull->getMessages()->count() - (($offset + 1) * $length);
            } else {
                $convertedOffset = 0;
            }

            $messages = $conversationOrNull->getMessages()->slice($convertedOffset, $length);
            $messagesCount = $conversationOrNull->getMessages()->count();
            $conversationOrNull->setMessages($messages);
            $conversationOrNull->setTempMessagesCount($messagesCount);
        }

        return $conversationOrNull;
    }

    /**
     * Returns in an array every conversation of the given $user. If $user doesn't have any
     * conversations then returns empty array.
     * 
     * @param User $user
     * @return array
     */
    public function getConversationListOfUser(User $user) {
        $conversations = $this->em->getRepository('CoreMessageBundle:Conversation')->findConversationsOfUser($user->getId());
        foreach ($conversations as &$conversationArray) {
            $conversationEntity = $conversationArray['conversation'];
            $conversationArray = $this->extendConversationWithPartnerDatasForList($conversationEntity, $user);
        }

        return $conversations;
    }

    /**
     * Extends the given $conversation with the partner (the other user of the conversation, not $participant) 
     * user's datas: username, id and also with the count of unread messages by $participant.
     * 
     * @param Conversation $conversation
     * @param User $participant
     * @return array
     */
    public function extendConversationWithPartnerDatasForList(Conversation $conversation, User $participant) {
        $conversationArray = array(
            'conversation' => $conversation
        );

        if ($conversation->getReciever() !== $participant) {
            $partner = $conversation->getReciever();
        } else {
            $partner = $conversation->getStarter();
        }

        $conversationArray['partner'] = array();
        $conversationArray['partner']['username'] = $partner->getUsername();
        $conversationArray['partner']['id'] = $partner->getId();
        $conversationArray['unreadMessagesCount'] = $conversation->getUnreadMessagesCountForUser($participant);

        return $conversationArray;
    }

    /**
     * Loads the conversatio between $currentUser and $otherUser and sets every message's
     * recieverSeenAt to now where the reciever is $currentUser and recieverSeenAt is null.
     * 
     * @param User $currentUser
     * @param User $otherUser
     * @return null
     */
    public function setConversationMessagesSeenAtForCurrentUser(User $currentUser, User $otherUser) {
        $conversation = $this->getOrCreateConversationForUsers($currentUser, $otherUser, null, null);

        if (count($conversation->getMessages()) > 0) {
            foreach ($conversation->getMessages() as $message) {
                if ($message->getAuthor() !== $currentUser && is_null($message->getRecieverSeenAt())) {
                    $message->setRecieverSeenAt(new \DateTime());
                    $this->em->persist($message);
                }
            }

            $this->em->flush();
        }

        return null;
    }

    /**
     * Returns the Conversation with the id of $conversationId or throws EntityNotFoundException
     * if no Conversation exists with that id.
     * 
     * @param int $conversationId
     * @return Conversation
     * @throws NotFoundEntityException
     */
    public function getConversationOr404($conversationId) {
        $conversation = $this->em->getRepository('CoreMessageBundle:Conversation')->find($conversationId);

        if (false === ($conversation instanceof Conversation)) {
            throw new NotFoundEntityException('message.no_conversation_with_given_id');
        }

        return $conversation;
    }

    /**
     * Returns the count of conversations which contain messages where the reciever is $user
     * and recieverSeenAt is null.
     * 
     * @param User $user
     * @return int
     */
    public function getConversationCountWithUnseenMessageForUser(User $user) {
        return $this->em->getRepository('CoreMessageBundle:Conversation')->getConversationCountWithUnseenMessage($user->getId());
    }

}
