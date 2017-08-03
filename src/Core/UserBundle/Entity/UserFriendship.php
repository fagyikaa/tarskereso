<?php

namespace Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Core\UserBundle\Entity\User;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="Core\UserBundle\Repository\UserFriendshipRepository")
 * @ORM\Table(name="user_friendship")
 * 
 * @JMS\ExclusionPolicy("all")
 */
class UserFriendship implements \JsonSerializable {

    const STATUS_PENDING = 1;
    const STATUS_ACCEPTED = 2;
    const STATUS_DECLINED = 3;
    const STATUS_BLOCKED = 4;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User", inversedBy="requestedFriendships")
     * @ORM\JoinColumn(name="requester_id", referencedColumnName="id")
     */
    protected $requester;

    /**
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User", inversedBy="invitedFriendships")
     * @ORM\JoinColumn(name="invited_id", referencedColumnName="id")
     */
    protected $invited;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     * @Assert\NotBlank(message="common.basic.not_blank")
     * @Assert\Type(
     *      type="integer",
     *      message="common.basic.integer"
     * )
     * @Assert\Range(
     *      min = UserFriendship::STATUS_PENDING,
     *      max = UserFriendship::STATUS_BLOCKED,
     *      minMessage = "common.basic.range_min",
     *      maxMessage = "common.basic.range_max"
     * )
     * 
     * @JMS\Expose
     * @JMS\Groups({"detailed"})
     */
    protected $status;
    
    /**
     * @ORM\Column(name="acknowledged_at", type="datetime", nullable=true)
     * @Assert\DateTime(message="common.basic.date_time")
     * 
     * @JMS\Expose
     * @JMS\Groups({"detailed"})
     */
    protected $acknowledgedAt;
    
    /**
     * @ORM\Column(name="invited_seen_at", type="datetime", nullable=true)
     * @Assert\DateTime(message="common.basic.date_time")
     * 
     * @JMS\Expose
     * @JMS\Groups({"detailed"})
     */
    protected $invitedSeenAt;

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }


    /**
     * Set status
     *
     * @param integer $status
     *
     * @return UserFriendship
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set acknowledgedAt
     *
     * @param DateTime $acknowledgedAt
     *
     * @return UserFriendship
     */
    public function setAcknowledgedAt($acknowledgedAt)
    {
        $this->acknowledgedAt = $acknowledgedAt;

        return $this;
    }

    /**
     * Get acknowledgedAt
     *
     * @return DateTime
     */
    public function getAcknowledgedAt()
    {
        return $this->acknowledgedAt;
    }

    /**
     * Set invitedSeenAt
     *
     * @param DateTime $invitedSeenAt
     *
     * @return UserFriendship
     */
    public function setInvitedSeenAt($invitedSeenAt)
    {
        $this->invitedSeenAt = $invitedSeenAt;

        return $this;
    }

    /**
     * Get invitedSeenAt
     *
     * @return DateTime
     */
    public function getInvitedSeenAt()
    {
        return $this->invitedSeenAt;
    }
    

    /**
     * Set requester
     *
     * @param User $requester
     *
     * @return UserFriendship
     */
    public function setRequester(User $requester = null)
    {
        $this->requester = $requester;

        return $this;
    }

    /**
     * Get requester
     *
     * @return User
     */
    public function getRequester()
    {
        return $this->requester;
    }

    /**
     * Set invited
     *
     * @param User $invited
     *
     * @return UserFriendship
     */
    public function setInvited(User $invited)
    {
        $this->invited = $invited;

        return $this;
    }

    /**
     * Get invited
     *
     * @return User
     */
    public function getInvited()
    {
        return $this->invited;
    }
    
    /**
     * Returns the id of $requester.
     * 
     * @return int
     * 
     * @JMS\VirtualProperty
     * @JMS\SerializedName("requesterId")
     */
    public function getRequesterId() {
        return $this->getRequester()->getId();
    }
    
    /**
     * Returns the id of $invited.
     * 
     * @return int
     * 
     * @JMS\VirtualProperty
     * @JMS\SerializedName("invitedId")
     */
    public function getInvitedId() {
        return $this->getInvited()->getId();
    }
    
    /**
     * Returns an array containing the requester user's datas.
     * 
     * @JMS\VirtualProperty
     * @JMS\SerializedName("requester_datas")
     * @JMS\Groups({"detailed", "websocket"})
     * 
     * @return array
     */
    public function getRequesterDatas() {
        $requester = $this->getRequester();
        return array(
            'id' => $requester->getId(),
            'username' => $requester->getUsername(),
            'age' => $requester->getAge(),
            'gender' => $requester->getGender(),
            'motto' => $requester->getUserIntroduction()->getMotto(),
            'settlement' => $requester->getAddress()->getSettlement(),
            'county' => $requester->getAddress()->getCounty(),
        );
    }
    
    /**
     * Returns an array containing the invited user's datas.
     * 
     * @JMS\VirtualProperty
     * @JMS\SerializedName("invited_datas")
     * @JMS\Groups({"detailed", "websocket"})
     * 
     * @return array
     */
    public function getInvitedDatas() {
        $invited = $this->getInvited();
        return array(
            'id' => $invited->getId(),
            'username' => $invited->getUsername(),
            'age' => $invited->getAge(),
            'gender' => $invited->getGender(),
            'motto' => $invited->getUserIntroduction()->getMotto(),
            'settlement' => $invited->getAddress()->getSettlement(),
            'county' => $invited->getAddress()->getCounty(),
        );
    }

    /**
     * Returns the fields which will be serialized by json_encode.
     * 
     * @return array
     */
    public function jsonSerialize() {
        return array(
            'id' => $this->getId(),
            'status' => $this->getStatus(),
            'acknowledgedAt' => $this->getAcknowledgedAt(),
            'invitedSeenAt' => $this->getInvitedSeenAt(),
            'invitedId' => $this->getInvitedId(),
            'requesterId' => $this->getRequesterId(),
        );
    }

}
