<?php

namespace Core\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Core\UserBundle\Entity\User;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Core\MediaBundle\Entity\Image;

/**
 * @ORM\Entity
 * @ORM\Table(name="votes")
 * 
 * @JMS\ExclusionPolicy("all")
 */
class Vote {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @JMS\Expose
     */
    protected $id;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @Assert\Type(
     *      type="integer",
     *      message="common.basic.integer"
     * )
     * @Assert\Range(
     *      min = 1,
     *      max = 5,
     *      minMessage = "common.basic.range_min",
     *      maxMessage = "common.basic.range_max"
     * )
     * 
     * @JMS\Expose
     */
    protected $stars;

    /**
     * @ORM\ManyToOne(targetEntity="Core\UserBundle\Entity\User", inversedBy="votes", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message = "media.vote.user.not_blank")
     * 
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Core\MediaBundle\Entity\Image", inversedBy="votes", cascade={"persist"})
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message = "media.vote.image.not_blank")
     * 
     * @JMS\Expose
     */
    protected $image;

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set stars
     *
     * @param int $stars
     *
     * @return Vote
     */
    public function setStars($stars) {
        $this->stars = $stars;

        return $this;
    }

    /**
     * Get stars
     *
     * @return int
     */
    public function getStars() {
        return $this->stars;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return Vote
     */
    public function setUser(User $user) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set image
     *
     * @param Image $image
     *
     * @return Vote
     */
    public function setImage(Image $image) {
        $this->image = $image;
        $this->image->addVote($this);
        
        return $this;
    }

    /**
     * Get image
     *
     * @return Image
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * If the owner of this vote has any other vote on $image then builds violation.
     * 
     * @Assert\Callback()
     */
    public function validate(ExecutionContextInterface $context) {
        if ($this->image->hasVoted($this->user)) {
            $i = 0;
            foreach ($this->image->getVotes() as $vote) {
                if ($vote->getUser() === $this->user) {
                    $i++;
                }
            }
            if ($i > 1) {
                $context->buildViolation('media.vote.already_voted')
                        ->addViolation();
            }
        }
    }

}
