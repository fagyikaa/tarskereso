<?php

namespace Core\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Core\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Core\MediaBundle\Entity\Vote;

/**
 * @ORM\Entity
 * @ORM\Table(name="images")
 * 
 * @Vich\Uploadable
 * 
 * @JMS\ExclusionPolicy("all")
 */
class Image {

    const MAX_FILE_SIZE = 5; // MB
    const FIELD_IS_PRIVATE = 'isPrivate';
    const FIELD_IS_PROFILE = 'isProfile';
    const FIELD_ABOUT = 'about';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @JMS\Expose
     */

    protected $id;

    /**
     * @Vich\UploadableField(mapping="core_media_image", fileNameProperty="name")
     * @Assert\File(
     *     mimeTypes = {"image/jpeg", "image/png"},
     *     mimeTypesMessage = "media.image.file.mime_type",
     * maxSize=5000000)
     */
    protected $file;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="media.image.name.length")
     * @Assert\Type(
     *      type="string",
     *      message="common.basic.string"
     * )   
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User", inversedBy="uploadedImages", cascade={"persist"})
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message = "media.image.owner.not_blank")
     * @Assert\Valid()
     */
    protected $owner;

    /**
     * @ORM\Column(name="uploaded_at", type="datetime", nullable=false)
     * @Assert\DateTime(message="common.basic.date_time")
     * @Assert\NotBlank(message = "media.image.uploaded_at.not_blank")
     * 
     * @JMS\Expose
     */
    protected $uploadedAt;

    /**
     * @ORM\Column(name="is_private", type="boolean")
     * @Assert\Type(
     *      type="bool",
     *      message="common.basic.bool",
     *      groups={"upload"}
     * )
     * 
     * @JMS\Expose
     */
    protected $isPrivate;

    /**
     * @ORM\Column(name="is_profile", type="boolean")
     * @Assert\Type(
     *      type="bool",
     *      message="common.basic.bool",
     *      groups={"upload"}
     * )
     * 
     * @JMS\Expose
     */
    protected $isProfile;

    /**
     * @ORM\Column(type="text", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="media.image.about.length", groups={"upload"})
     * @Assert\Type(
     *      type="string",
     *      message="common.basic.string",
     *      groups={"upload"}
     * )
     * 
     * @JMS\Expose
     */
    protected $about;

    /**
     * @ORM\OneToMany(targetEntity="Core\MediaBundle\Entity\Vote", mappedBy="image", cascade={"persist", "remove"})
     */
    protected $votes;

    public function __construct() {
        $this->uploadedAt = new \DateTime();
        $this->votes = new ArrayCollection();
    }

    /**
     * Set file
     * 
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     * 
     * @param File|UploadedFile $tmp
     */
    public function setFile(File $tmp) {
        $this->file = $tmp;

        return $this;
    }

    /**
     * Get file
     * 
     * @return File
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Image
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set uploadedAt
     *
     * @param \DateTime $uploadedAt
     * @return Image
     */
    public function setUploadedAt($uploadedAt) {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    /**
     * Get uploadedAt
     *
     * @return \DateTime 
     */
    public function getUploadedAt() {
        return $this->uploadedAt;
    }

    /**
     * Set owner
     *
     * @param User $owner
     * @return Image
     */
    public function setOwner(User $owner) {
        $this->owner = $owner;
        $owner->addUploadedImage($this);

        return $this;
    }

    /**
     * Get owner
     *
     * @return User 
     */
    public function getOwner() {
        return $this->owner;
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
     * Set isPrivate
     *
     * @param boolean $isPrivate
     *
     * @return Image
     */
    public function setIsPrivate($isPrivate) {
        $this->isPrivate = $isPrivate;
        if ($isPrivate) {
            $this->isProfile = false;
        }

        return $this;
    }

    /**
     * Get isPrivate
     *
     * @return boolean
     */
    public function getIsPrivate() {
        return $this->isPrivate;
    }

    /**
     * Set isProfile
     *
     * @param boolean $isProfile
     *
     * @return Image
     */
    public function setIsProfile($isProfile) {
        $this->isProfile = $isProfile;
        if ($isProfile) {
            $this->isPrivate = false;
        }

        return $this;
    }

    /**
     * Get isProfile
     *
     * @return boolean
     */
    public function getIsProfile() {
        return $this->isProfile;
    }

    /**
     * Set about
     *
     * @param string $about
     *
     * @return Image
     */
    public function setAbout($about) {
        $this->about = $about;

        return $this;
    }

    /**
     * Get about
     *
     * @return string
     */
    public function getAbout() {
        return $this->about;
    }

    /**
     * If $user is the owner or a friend of user then returns true, false otherwise.
     * 
     * @param User $user
     * @return boolean
     */
    public function isViewAbleFor(User $user) {
        if ($this->owner === $user) {
            return true;
        } else {
            return $this->owner->isFriendWith($user);
        }
    }

    /**
     * Add vote
     *
     * @param Vote $vote
     *
     * @return Image
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
     * Return true if $user has voted on this image, false otherwise.
     * 
     * @param User $user
     * @return boolean
     */
    public function hasVoted(User $user) {
        foreach ($this->votes as $vote) {
            if ($vote->getUser() === $user) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the vote of $user or null if $user hasn't voted yet.
     * 
     * @param User $user
     * @return Vote|null
     */
    public function getVoteOf(User $user) {
        foreach ($this->votes as $vote) {
            if ($vote->getUser() === $user) {
                return $vote;
            }
        }

        return null;
    }

    /**
     * Returns the average stars count of votes or -1 if no vote has been added yet. 
     * 
     * @return int
     * 
     * @JMS\VirtualProperty
     * @JMS\SerializedName("voteAverage")
     */
    public function getVoteAverage() {
        if ($this->votes->count() === 0) {
            return -1;
        }

        $sum = 0;
        foreach ($this->votes as $vote) {
            $sum += $vote->getStars();
        }

        return number_format($sum / $this->votes->count(), 1);
    }

    /**
     * If isPrivate and isProfile both are true then builds violation.
     * 
     * @Assert\Callback(groups={"upload", "Default"})
     */
    public function validate(ExecutionContextInterface $context) {
        if ($this->isPrivate && $this->isProfile) {
            $context->buildViolation('media.image.private_profile_not_allowed')
                    ->addViolation();
        }
    }

}
