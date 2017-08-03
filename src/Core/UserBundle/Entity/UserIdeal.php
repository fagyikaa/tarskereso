<?php

namespace Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Core\UserBundle\Entity\UserPersonal;
use Core\CommonBundle\Entity\Address;
use Core\UserBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_ideal")
 */
class UserIdeal extends UserPersonal {

    //Fields
    const FIELD_HEIGHT_FROM = 'heightFrom';
    const FIELD_HEIGHT_TO = 'heightTo';
    const FIELD_WEIGHT_FROM = 'weightFrom';
    const FIELD_WEIGHT_TO = 'weightTo';
    const FIELD_AGE_FROM = 'ageFrom';
    const FIELD_AGE_TO = 'ageTo';
    const FIELD_GENDER = 'gender';
    const FIELD_ADDRESS = 'address';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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
    protected $heightFrom;

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
    protected $heightTo;
    
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
    protected $weightFrom;

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
    protected $weightTo;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(
     *      type="integer",
     *      message="common.basic.integer"
     * )
     * @Assert\Range(
     *      min = 18,
     *      max = 100,
     *      minMessage = "common.basic.range_min",
     *      maxMessage = "common.basic.range_max"
     * )
     */
    protected $ageFrom;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(
     *      type="integer",
     *      message="common.basic.integer"
     * )
     * @Assert\Range(
     *      min = 18,
     *      max = 100,
     *      minMessage = "common.basic.range_min",
     *      maxMessage = "common.basic.range_max"
     * )
     */
    protected $ageTo;
    
    /**
     * @ORM\Column(type="string", length=6, nullable=true)
     * @Assert\Choice(
     *     choices = {
     *         User::GENDER_MALE,
     *         User::GENDER_FEMALE
     *     }, 
     *  message = "user.gender.not_valid"
     * )
     */
    protected $gender;
    
    /**
     * @ORM\ManyToOne(targetEntity="Core\CommonBundle\Entity\Address")
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", nullable=true)
     */
    protected $address; 
      
    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }  


    /**
     * Set heightFrom
     *
     * @param int $heightFrom
     *
     * @return UserIdeal
     */
    public function setHeightFrom($heightFrom)
    {
        $this->heightFrom = $heightFrom;

        return $this;
    }

    /**
     * Get heightFrom
     *
     * @return int
     */
    public function getHeightFrom()
    {
        return $this->heightFrom;
    }

    /**
     * Set heightTo
     *
     * @param int $heightTo
     *
     * @return UserIdeal
     */
    public function setHeightTo($heightTo)
    {
        $this->heightTo = $heightTo;

        return $this;
    }

    /**
     * Get heightTo
     *
     * @return int
     */
    public function getHeightTo()
    {
        return $this->heightTo;
    }

    /**
     * Set weightFrom
     *
     * @param int $weightFrom
     *
     * @return UserIdeal
     */
    public function setWeightFrom($weightFrom)
    {
        $this->weightFrom = $weightFrom;

        return $this;
    }

    /**
     * Get weightFrom
     *
     * @return int
     */
    public function getWeightFrom()
    {
        return $this->weightFrom;
    }

    /**
     * Set weightTo
     *
     * @param int $weightTo
     *
     * @return UserIdeal
     */
    public function setWeightTo($weightTo)
    {
        $this->weightTo = $weightTo;

        return $this;
    }

    /**
     * Get weightTo
     *
     * @return int
     */
    public function getWeightTo()
    {
        return $this->weightTo;
    }

    /**
     * Set ageFrom
     *
     * @param int $ageFrom
     *
     * @return UserIdeal
     */
    public function setAgeFrom($ageFrom)
    {
        $this->ageFrom = $ageFrom;

        return $this;
    }

    /**
     * Get ageFrom
     *
     * @return int
     */
    public function getAgeFrom()
    {
        return $this->ageFrom;
    }

    /**
     * Set ageTo
     *
     * @param int $ageTo
     *
     * @return UserIdeal
     */
    public function setAgeTo($ageTo)
    {
        $this->ageTo = $ageTo;

        return $this;
    }

    /**
     * Get ageTo
     *
     * @return int
     */
    public function getAgeTo()
    {
        return $this->ageTo;
    }

    /**
     * Set gender
     *
     * @param int $gender
     *
     * @return UserIdeal
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return int
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set address
     *
     * @param Address $address
     *
     * @return UserIdeal
     */
    public function setAddress(Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }
}
