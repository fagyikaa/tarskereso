<?php

namespace Core\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;
use Core\UserBundle\Entity\User;
use Core\MessageBundle\Entity\Message;

/**
 * @ORM\Entity(repositoryClass="Core\MessageBundle\Repository\ConversationRepository")
 * @ORM\Table(name="conversations")
 * 
 * @JMS\ExclusionPolicy("all")
 */
class Conversation {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @JMS\Expose
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User", inversedBy="startedConversations", cascade={"persist"})
     * @ORM\JoinColumn(name="starter_id", referencedColumnName="id")
     */
    protected $starter;

    /**
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User", inversedBy="recievedConversations", cascade={"persist"})
     * @ORM\JoinColumn(name="reciever_id", referencedColumnName="id")
     */
    protected $reciever;

    /**
     * @ORM\OneToMany(targetEntity="Core\MessageBundle\Entity\Message", mappedBy="conversation", cascade={"persist", "remove"})
     *
     * @JMS\Expose
     * @JMS\Groups({"detailed"})
     */
    protected $messages;

    /**
     * Temporarily contains the messages count, needs for frontend
     *
     * @JMS\Expose
     * @JMS\Groups({"detailed"})
     */
    protected $tempMessagesCount;

    public function __construct() {
        $this->messages = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set starter
     *
     * @param User $starter
     *
     * @return Conversation
     */
    public function setStarter(User $starter) {
        $this->starter = $starter;

        return $this;
    }

    /**
     * Get starter
     *
     * @return User
     */
    public function getStarter() {
        return $this->starter;
    }

    /**
     * Set reciever
     *
     * @param User $reciever
     *
     * @return ArrayConversation
     */
    public function setReciever(User $reciever) {
        $this->reciever = $reciever;

        return $this;
    }

    /**
     * Get reciever
     *
     * @return User
     */
    public function getReciever() {
        return $this->reciever;
    }

    /**
     * Add message
     *
     * @param Message $message
     *
     * @return Conversation
     */
    public function addMessage(Message $message) {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * Remove message
     *
     * @param Message $message
     */
    public function removeMessage(Message $message) {
        $this->messages->removeElement($message);
    }

    /**
     * Get messages
     *
     * @return ArrayCollection
     */
    public function getMessages() {
        return $this->messages;
    }

    /**
     * Returns the counf os messages where $participant is the reciever and recieverSeenAt is null.
     * 
     * @param User $participant
     * @return int
     */
    public function getUnreadMessagesCountForUser(User $participant) {
        if ($this->getStarter() !== $participant && $this->getReciever() !== $participant) {
            return 0;
        }

        $sum = 0;
        foreach ($this->messages as $message) {
            if ($message->getAuthor() !== $participant && is_null($message->getRecieverSeenAt())) {
                $sum++;
            }
        }

        return $sum;
    }

    /**
     * Returns an array containing the last message and the author's id.
     * 
     * @JMS\VirtualProperty
     * @JMS\SerializedName("lastMessage")
     * @JMS\Groups({"list"})
     * 
     * @return array
     */
    public function getLastMessage() {
        return $this->getMessages()->last();
    }

    /**
     * Set messages to $messages
     * 
     * @param array $messages
     */
    public function setMessages(array $messages) {
        $this->messages = new ArrayCollection($messages);
    }

    public function setTempMessagesCount($messagesCount) {
        $this->tempMessagesCount = $messagesCount;
    }

    public function getTempMessagesCount() {
        return $this->tempMessagesCount;
    }

}
