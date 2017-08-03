<?php

namespace Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Core\UserBundle\Entity\UserPersonal;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_introductions")
 */
class UserIntroduction extends UserPersonal {

    //Fields
    const FIELD_MOTTO = 'motto';
    const FIELD_INTRODUTION = 'introduction';
    const FIELD_HEIGHT = 'height';
    const FIELD_WEIGHT = 'weight';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="common.basic.too_long")
     * @Assert\Type(
     *      type="string",
     *      message="common.basic.string"
     * )
     */
    protected $motto;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     * @Assert\Length(max=1024, maxMessage="common.basic.too_long")
     * @Assert\Type(
     *      type="string",
     *      message="common.basic.string"
     * )
     */
    protected $introduction;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(
     *      type="integer",
     *      message="common.basic.integer"
     * )
     * @Assert\Range(
     *      min = 50,
     *      max = 300,
     *      minMessage = "common.basic.range_min",
     *      maxMessage = "common.basic.range_max"
     * )
     */
    protected $height;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(
     *      type="integer",
     *      message="common.basic.integer"
     * )
     * @Assert\Range(
     *      min = 30,
     *      max = 400,
     *      minMessage = "common.basic.range_min",
     *      maxMessage = "common.basic.range_max"
     * )
     */
    protected $weight;

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set motto
     *
     * @param string $motto
     *
     * @return UserIntroduction
     */
    public function setMotto($motto) {
        $this->motto = $motto;

        return $this;
    }

    /**
     * Get motto
     *
     * @return string
     */
    public function getMotto() {
        return $this->motto;
    }

    /**
     * Set introduction
     *
     * @param string $introduction
     *
     * @return UserIntroduction
     */
    public function setIntroduction($introduction) {
        $this->introduction = $introduction;

        return $this;
    }

    /**
     * Get introduction
     *
     * @return string
     */
    public function getIntroduction() {
        return $this->introduction;
    }

    /**
     * Set height
     *
     * @param int $height
     *
     * @return UserIntroduction
     */
    public function setHeight($height) {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return int
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * Set weight
     *
     * @param int $weight
     *
     * @return UserIntroduction
     */
    public function setWeight($weight) {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return int
     */
    public function getWeight() {
        return $this->weight;
    }

}
