<?php

namespace Core\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Core\CommonBundle\Entity\Address;
use Core\UserBundle\Entity\UserIntroduction;
use Core\UserBundle\Entity\UserIdeal;
use Core\UserBundle\Entity\UserFriendship;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Core\MediaBundle\Entity\Image;
use Core\MediaBundle\Entity\Vote;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Core\MessageBundle\Entity\Conversation;
use Core\MessageBundle\Entity\Message;

/**
 * @ORM\Entity(repositoryClass="Core\UserBundle\Repository\UserRepository")
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 * @ORM\Table(name="users")
 */
class User extends BaseUser {

    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';
    const FIELD_BIRTH_DATE = 'birthDate';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=2, nullable=false)
     * @Assert\Length(max=2, maxMessage="user.language.length")
     * @Assert\Locale(message="user.language.locale")
     */
    protected $language;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     * @Assert\NotBlank(message="user.created_at.not_blank")
     * @Assert\DateTime(message="user.created_at.date_time")
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     * @Assert\DateTime(message="user.deleted_at.date_time")
     */
    protected $deletedAt;

    /**
     * @ORM\Column(type="string", length=6, nullable=false)
     * @Assert\Choice(
     *     choices = {
     *         User::GENDER_MALE,
     *         User::GENDER_FEMALE
     *     }, 
     *  message = "user.gender.not_valid"
     * )
     * @Assert\NotBlank(message="user.gender.not_blank")
     */
    protected $gender;

    /**
     * @ORM\Column(name="birth_date", type="date", nullable=false)
     * @Assert\NotBlank(message="user.birth_date.not_blank")
     * @Assert\Date(message="user.birth_date.date")
     * @Assert\LessThan(
     *      value = "-18 years",
     *      message = "user.birth_date.under18"
     * )
     */
    protected $birthDate;

    /**
     * @ORM\ManyToOne(targetEntity="Core\CommonBundle\Entity\Address", inversedBy="users")
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(message="user.address.not_blank")
     */
    protected $address;

    /**
     * @Assert\Length(min=6, max=32, minMessage="user.password.min_length", maxMessage="user.password.max_length")
     */
    protected $plainPassword;

    /**
     * @ORM\OneToOne(targetEntity="Core\UserBundle\Entity\UserIntroduction", cascade={"persist", "remove"})
     */
    protected $userIntroduction;

    /**
     * @ORM\OneToOne(targetEntity="Core\UserBundle\Entity\UserIdeal", cascade={"persist", "remove"})
     */
    protected $userIdeal;

    /**
     * @ORM\OneToMany(targetEntity="Core\MediaBundle\Entity\Image", mappedBy="owner", cascade={"persist", "remove"})
     */
    protected $uploadedImages;

    /**
     * @ORM\OneToMany(targetEntity="Core\MediaBundle\Entity\Vote", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $votes;

    /**
     * @ORM\OneToOne(targetEntity="Core\UserBundle\Entity\UserSearch", cascade={"persist", "remove"})
     */
    protected $userSearch;

    /**
     * @ORM\OneToMany(targetEntity="Core\UserBundle\Entity\UserFriendship", mappedBy="requester", cascade={"persist", "remove"})
     */
    protected $requestedFriendships;

    /**
     * @ORM\OneToMany(targetEntity="Core\UserBundle\Entity\UserFriendship", mappedBy="invited", cascade={"persist", "remove"})
     */
    protected $invitedFriendships;
    
    /**
     * @ORM\OneToMany(targetEntity="Core\MessageBundle\Entity\Conversation", mappedBy="starter", cascade={"persist", "remove"})
     */
    protected $startedConversations;
    
    /**
     * @ORM\OneToMany(targetEntity="Core\MessageBundle\Entity\Conversation", mappedBy="reciever", cascade={"persist", "remove"})
     */
    protected $recievedConversations;
    
    /**
     * @ORM\OneToMany(targetEntity="Core\MessageBundle\Entity\Message", mappedBy="author", cascade={"persist", "remove"})
     */
    protected $messages;

    public function __construct() {
        parent::__construct();
        $this->createdAt = new \DateTime();
        $this->uploadedImages = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->requestedFriendships = new ArrayCollection();
        $this->invitedFriendships = new ArrayCollection();
        $this->startedConversations = new ArrayCollection();
        $this->recievedConversations = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return User
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
     * Get language
     * 
     * @return string
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * Set language
     * 
     * @param string $language
     * @return User
     */
    public function setLanguage($language) {
        $this->language = $language;

        return $this;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return User
     */
    public function setDeletedAt($deletedAt) {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime 
     */
    public function getDeletedAt() {
        return $this->deletedAt;
    }

    /**
     * Set gender
     *
     * @param string $gender
     *
     * @return User
     */
    public function setGender($gender) {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return int
     */
    public function getGender() {
        return $this->gender;
    }

    /**
     * Set birthDate
     *
     * @param \DateTime $birthDate
     *
     * @return User
     */
    public function setBirthDate($birthDate) {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Get birthDate
     *
     * @return \DateTime
     */
    public function getBirthDate() {
        return $this->birthDate;
    }

    /**
     * Set address
     *
     * @param Address $address
     *
     * @return User
     */
    public function setAddress(Address $address) {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return Address
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * Get age of user.
     * 
     * @return int
     */
    public function getAge() {
        return $this->birthDate->diff(new \DateTime())->format('%y');
    }

    /**
     * Set userIntroduction
     *
     * @param UserIntroduction $userIntroduction
     *
     * @return User
     */
    public function setUserIntroduction(UserIntroduction $userIntroduction = null) {
        $this->userIntroduction = $userIntroduction;

        return $this;
    }

    /**
     * Get userIntroduction
     *
     * @return UserIntroduction
     */
    public function getUserIntroduction() {
        return $this->userIntroduction;
    }

    /**
     * Set usserIdeal
     *
     * @param UserIDeal $userIdeal
     *
     * @return User
     */
    public function setUserIdeal(UserIdeal $userIdeal = null) {
        $this->userIdeal = $userIdeal;

        return $this;
    }

    /**
     * Get userIdeal
     *
     * @return UserIdeal
     */
    public function getUserIdeal() {
        return $this->userIdeal;
    }

    /**
     * Set uploadedImages
     * 
     * @param array $images
     * @return User
     */
    public function setUploadedImages($images) {
        $this->uploadedImages = $images;

        return $this;
    }

    /**
     * Get uploadedImages
     * 
     * @return ArrayCollection
     */
    public function getUploadedImages() {
        return $this->uploadedImages;
    }

    /**
     * Adds $image to uploadedImages. If $image's isProfile is true then set every
     * other image's isProfile property to false.
     * 
     * @param Image $image
     * @return User
     */
    public function addUploadedImage(Image $image) {
        if ($image->getIsProfile()) {
            foreach ($this->getUploadedImages() as $storedImage) {
                $storedImage->setIsProfile(false);
            }
        }
        $this->uploadedImages[] = $image;

        return $this;
    }

    /**
     * Returns true if user has any uploaded images, false otherwise.
     * 
     * @return boolean
     */
    public function hasUploadedImage() {
        return ($this->getUploadedImages()->count()) > 0;
    }

    /**
     * Returns true if the user has profile image, false otherwise.
     * 
     * @return boolean
     */
    public function hasProfileImage() {
        foreach ($this->getUploadedImages() as $image) {
            if ($image->getIsProfile()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the id of the user's profile picture or 0 if the user doesn't have one.
     * 
     * @return int
     */
    public function getProfileImageId() {
        $imageOrNull = $this->getProfileImage();
        if (false === is_null($imageOrNull)) {
            return $imageOrNull->getId();
        }

        return 0;
    }

    /**
     * Returns the user's profile image or null if hasn't been uploaded yet.
     * 
     * @return Image|null
     */
    public function getProfileImage() {
        foreach ($this->getUploadedImages() as $image) {
            if ($image->getIsProfile()) {
                return $image;
            }
        }

        return null;
    }

    /**
     * Returns every uploaded public images of user.
     * 
     * @return array
     */
    public function getPublicImages() {
        $images = array();
        foreach ($this->getUploadedImages() as $image) {
            if (false === $image->getIsPrivate()) {
                $images[] = $image;
            }
        }

        return $images;
    }

    /**
     * Returns every uploaded private images of user.
     * 
     * @return array
     */
    public function getPrivateImages() {
        $images = array();
        foreach ($this->getUploadedImages() as $image) {
            if ($image->getIsPrivate()) {
                $images[] = $image;
            }
        }

        return $images;
    }

    /**
     * If $image is contained in uploadedImages and isProfile is true then
     * set every other image's isProfile property to false in uploadedImages.
     * 
     * @param Image $image
     */
    public function setEveryOtherImageNotProfileIfThisIs(Image $image) {
        if ($image->getIsProfile() && $this->getUploadedImages()->contains($image)) {
            foreach ($this->getUploadedImages() as $uploadedImage) {
                if ($uploadedImage !== $image) {
                    $uploadedImage->setIsProfile(false);
                }
            }
        }
    }

    /**
     * Add vote
     *
     * @param Vote $vote
     *
     * @return User
     */
    public function addVote(Vote $vote) {
        $this->votes[] = $vote;

        return $this;
    }

    /**
     * Remove vote
     *
     * @param Vote $vote
     */
    public function removeVote(Vote $vote) {
        $this->votes->removeElement($vote);
    }

    /**
     * Get votes
     *
     * @return ArrayCollection
     */
    public function getVotes() {
        return $this->votes;
    }

    /**
     * Remove uploadedImage
     *
     * @param Image $uploadedImage
     */
    public function removeUploadedImage(Image $uploadedImage) {
        $this->uploadedImages->removeElement($uploadedImage);
    }

    /**
     * Set userSearch
     *
     * @param UserSearch $userSearch
     *
     * @return User
     */
    public function setUserSearch(UserSearch $userSearch = null) {
        $this->userSearch = $userSearch;

        return $this;
    }

    /**
     * Get userSearch
     *
     * @return UserSearch
     */
    public function getUserSearch() {
        return $this->userSearch;
    }

    /**
     * If there are more then one profile picture then builds violation.
     * 
     * @Assert\Callback()
     */
    public function validate(ExecutionContextInterface $context) {
        $i = 0;
        foreach ($this->getUploadedImages() as $image) {
            if ($image->getIsProfile()) {
                $i++;
            }
        }

        if ($i > 1) {
            $context->buildViolation('user.uploaded_images.only_one_profile')
                    ->addViolation();
        }
    }

    /**
     * Add requestedFriendship
     *
     * @param UserFriendship $requestedFriendship
     *
     * @return User
     */
    public function addRequestedFriendship(UserFriendship $requestedFriendship) {
        $this->requestedFriendships[] = $requestedFriendship;

        return $this;
    }

    /**
     * Remove requestedFriendship
     *
     * @param UserFriendship $requestedFriendship
     */
    public function removeRequestedFriendship(UserFriendship $requestedFriendship) {
        $this->requestedFriendships->removeElement($requestedFriendship);
    }

    /**
     * Get requestedFriendships
     *
     * @return ArrayCollection
     */
    public function getRequestedFriendships() {
        return $this->requestedFriendships;
    }

    /**
     * Add invitedFriendship
     *
     * @param UserFriendship $invitedFriendship
     *
     * @return User
     */
    public function addInvitedFriendship(UserFriendship $invitedFriendship) {
        $this->invitedFriendships[] = $invitedFriendship;

        return $this;
    }

    /**
     * Remove invitedFriendship
     *
     * @param UserFriendship $invitedFriendship
     */
    public function removeInvitedFriendship(UserFriendship $invitedFriendship) {
        $this->invitedFriendships->removeElement($invitedFriendship);
    }

    /**
     * Get invitedFriendships
     *
     * @return ArrayCollection
     */
    public function getInvitedFriendships() {
        return $this->invitedFriendships;
    }

    /**
     * If this user has any UserFriendship with the given $user then returns that,
     * returns null otherwise.
     * 
     * @param User $user
     * @return UserFriendship|null
     */
    public function getUserFriendshipWith(User $user) {
        foreach ($this->getRequestedFriendships() as $friendship) {
            if ($friendship->getInvited() === $user) {
                return $friendship;
            }
        }

        foreach ($this->getInvitedFriendships() as $friendship) {
            if ($friendship->getRequester() === $user) {
                return $friendship;
            }
        }

        return null;
    }

    /**
     * If this user has any UserFriendship with the given $user then checks if the 
     * status is BLOCKED, returns false if no UserFriendship exists among this two user.
     * 
     * @param User $user
     * @return boolean
     */
    public function isBlockedWith(User $user) {
        return $this->checkGivenFriendshipStatusWith($user, UserFriendship::STATUS_BLOCKED);
    }
    
    /**
     * Checks if this user is blocked by the given $user. Returns false also if this user
     * has blocked $user.
     * 
     * @param User $user
     * @return boolean
     */
    public function isBlockeBy(User $user) {
        foreach ($this->getInvitedFriendships() as $friendship) {
            if ($friendship->getRequester() === $user) {
                return $friendship->getStatus() === UserFriendship::STATUS_BLOCKED;
            }
        }
        
        return false;
    }

    /**
     * If this user has any UserFriendship with the given $user then checks if the 
     * status is ACCEPTED, returns false if no UserFriendship exists among this two user.
     * 
     * @param User $user
     * @return boolean
     */
    public function isFriendWith(User $user) {
        return $this->checkGivenFriendshipStatusWith($user, UserFriendship::STATUS_ACCEPTED);
    }

    /**
     * If this user has any UserFriendship with the given $user then checks if the 
     * status is PENDING, returns false if no UserFriendship exists among this two user.
     * 
     * @param User $user
     * @return boolean
     */
    public function isPendingWith(User $user) {
        return $this->checkGivenFriendshipStatusWith($user, UserFriendship::STATUS_PENDING);
    }

    /**
     * If this user has any UserFriendship with the given $user then checks if the 
     * status is DECLINED, returns false if no UserFriendship exists among this two user.
     * 
     * @param User $user
     * @return boolean
     */
    public function isDeclinedWith(User $user) {
        return $this->checkGivenFriendshipStatusWith($user, UserFriendship::STATUS_DECLINED);
    }

    /**
     * If this user has any UserFriendship with the given $user then checks if the 
     * status is $status, returns false if no UserFriendship exists among this two user.
     * 
     * @param User $user
     * @return boolean
     */
    public function checkGivenFriendshipStatusWith(User $user, $status) {
        $friendship = $this->getUserFriendshipWith($user);
        if (is_null($friendship)) {
            return false;
        }

        return $friendship->getStatus() === $status;
    }


    /**
     * Add startedConversation
     *
     * @param Conversation $startedConversation
     *
     * @return User
     */
    public function addStartedConversation(Conversation $startedConversation)
    {
        $this->startedConversations[] = $startedConversation;

        return $this;
    }

    /**
     * Remove startedConversation
     *
     * @param Conversation $startedConversation
     */
    public function removeStartedConversation(Conversation $startedConversation)
    {
        $this->startedConversations->removeElement($startedConversation);
    }

    /**
     * Get startedConversations
     *
     * @return ArrayCollection
     */
    public function getStartedConversations()
    {
        return $this->startedConversations;
    }

    /**
     * Add recievedConversation
     *
     * @param Conversation $recievedConversation
     *
     * @return User
     */
    public function addRecievedConversation(Conversation $recievedConversation)
    {
        $this->recievedConversations[] = $recievedConversation;

        return $this;
    }

    /**
     * Remove recievedConversation
     *
     * @param Conversation $recievedConversation
     */
    public function removeRecievedConversation(Conversation $recievedConversation)
    {
        $this->recievedConversations->removeElement($recievedConversation);
    }

    /**
     * Get recievedConversations
     *
     * @return ArrayCollection
     */
    public function getRecievedConversations()
    {
        return $this->recievedConversations;
    }

    /**
     * Add message
     *
     * @param Message $message
     *
     * @return User
     */
    public function addMessage(Message $message)
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * Remove message
     *
     * @param Message $message
     */
    public function removeMessage(Message $message)
    {
        $this->messages->removeElement($message);
    }

    /**
     * Get messages
     *
     * @return ArrayCollection
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
