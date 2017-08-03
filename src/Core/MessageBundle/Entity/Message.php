<?php

namespace Core\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Core\UserBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="messages")
 * 
 * @JMS\ExclusionPolicy("all")
 */
class Message {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @JMS\Expose
     * @JMS\Groups({"Default", "websocket"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User", inversedBy="messages")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * @ORM\Column(type="string", length=1024, nullable=false)
     * @Assert\NotBlank(message="common.basic.not_blank")
     * @Assert\Length(max=1024, maxMessage="common.basic.too_long")
     * @Assert\Type(
     *      type="string",
     *      message="common.basic.string"
     * )
     * 
     * @JMS\Expose
     * @JMS\Groups({"Default", "websocket"})
     */
    protected $text;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     * @Assert\DateTime(message="common.basic.date_time")
     * 
     * @JMS\Expose
     * @JMS\Groups({"Default", "websocket"})
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="reciever_seen_at", type="datetime", nullable=true)
     * @Assert\DateTime(message="common.basic.date_time")
     * 
     * @JMS\Expose
     * @JMS\Groups({"Default", "websocket"})
     */
    protected $recieverSeenAt;

    /**
     * @ORM\ManyToOne(targetEntity="Core\MessageBundle\Entity\Conversation", inversedBy="messages")
     * @ORM\JoinColumn(name="conversation_id", referencedColumnName="id", nullable=false)
     */
    protected $conversation;

    public function __construct() {
        $this->createdAt = new \DateTime();
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
     * Set text
     *
     * @param string $text
     *
     * @return Message
     */
    public function setText($text) {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Message
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set recieverSeenAt
     *
     * @param \DateTime $recieverSeenAt
     *
     * @return Message
     */
    public function setRecieverSeenAt($recieverSeenAt) {
        $this->recieverSeenAt = $recieverSeenAt;

        return $this;
    }

    /**
     * Get recieverSeenAt
     *
     * @return \DateTime
     */
    public function getRecieverSeenAt() {
        return $this->recieverSeenAt;
    }

    /**
     * Set author
     *
     * @param User $author
     *
     * @return Message
     */
    public function setAuthor(User $author) {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return User
     */
    public function getAuthor() {
        return $this->author;
    }

    /**
     * Returns the id of the author
     * 
     * @JMS\VirtualProperty
     * @JMS\SerializedName("authorId")
     * @JMS\Groups({"Default", "websocket"})
     * 
     * @return array
     */
    public function getAuthorId() {
        return $this->getAuthor()->getId();
    }

    /**
     * Returns the id of the author
     * 
     * @JMS\VirtualProperty
     * @JMS\SerializedName("recieverId")
     * @JMS\Groups({"Default", "websocket"})
     * 
     * @return array
     */
    public function getRecieverId() {
        $conversationStarter = $this->getConversation()->getStarter();

        return $conversationStarter === $this->getAuthor() ? $this->getConversation()->getReciever()->getId() : $conversationStarter->getId();
    }

    /**
     * Returns the id of the conversation
     * 
     * @JMS\VirtualProperty
     * @JMS\SerializedName("conversationId")
     * @JMS\Groups({"Default", "websocket"})
     * 
     * @return array
     */
    public function getConversationId() {
        return $this->getConversation()->getId();
    }

    /**
     * Set conversation
     *
     * @param Conversation $conversation
     *
     * @return Message
     */
    public function setConversation(Conversation $conversation) {
        $this->conversation = $conversation;

        return $this;
    }

    /**
     * Get conversation
     *
     * @return Conversation
     */
    public function getConversation() {
        return $this->conversation;
    }

}
