<?php

namespace Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Core\UserBundle\Entity\UserPersonal;
use Core\CommonBundle\Entity\Address;
use Core\UserBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_search")
 */
class UserSearch {

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
     * @ORM\Column(type="array", nullable=true)
     * @Assert\All({
     *      @Assert\Type(
     *          type="string",
     *          message="common.basic.string"
     *      ),
     *      @Assert\Choice(
     *          choices = {
     *              User::GENDER_MALE,
     *              User::GENDER_FEMALE
     *          }, 
     *      message = "user.gender.not_valid"
     *      )
     * })
     */
    protected $gender;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Type(
     *      type="string",
     *      message="common.basic.string"
     * )
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "common.basic.range_max"
     * )
     */
    protected $county;

    /**
     * @ORM\ManyToOne(targetEntity="Core\CommonBundle\Entity\Address")
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", nullable=true)
     */
    protected $address;

    /**
     * @ORM\Column(name="hair_color", type="array", nullable=true)
     * @Assert\All({
     *      @Assert\Type(
     *          type="string",
     *          message="common.basic.string"
     *      ),
     *      @Assert\Choice(
     *          choices = {
     *              UserPersonal::HAIR_COLOR_BLACK,
     *              UserPersonal::HAIR_COLOR_BROWN,
     *              UserPersonal::HAIR_COLOR_BLOND,
     *              UserPersonal::HAIR_COLOR_GRAY,
     *              UserPersonal::HAIR_COLOR_RED,
     *              UserPersonal::HAIR_COLOR_GREEN,  
     *              UserPersonal::HAIR_COLOR_BLLUE,
     *              UserPersonal::HAIR_COLOR_PINK,
     *              UserPersonal::HAIR_COLOR_OTHER
     *          }, 
     *          message = "common.basic.invalid_value"
     *      )
     * })
     */
    protected $hairColor;

    /**
     * @ORM\Column(name="hair_length", type="array", nullable=true)
     * @Assert\All({
     *      @Assert\Type(
     *          type="string",
     *          message="common.basic.string"
     *      ),
     *      @Assert\Choice(
     *          choices = {
     *              UserPersonal::HAIR_LENGTH_BALD,
     *              UserPersonal::HAIR_LENGTH_SHORT,
     *              UserPersonal::HAIR_LENGTH_MIDDLE,
     *              UserPersonal::HAIR_LENGTH_LONG 
     *          }, 
     *          message = "common.basic.invalid_value"
     *      )
     * })
     */
    protected $hairLength;

    /**
     * @ORM\Column(name="eye_color", type="array", nullable=true)
     * @Assert\All({
     *      @Assert\Type(
     *          type="string",
     *          message="common.basic.string"
     *      ),
     *      @Assert\Choice(
     *          choices = {
     *              UserPersonal::EYE_COLOR_GREEN,
     *              UserPersonal::EYE_COLOR_BLUE,
     *              UserPersonal::EYE_COLOR_BROWN,
     *              UserPersonal::EYE_COLOR_OTHER  
     *           }, 
     *            message = "common.basic.invalid_value"
     *      )
     * })
     */
    protected $eyeColor;

    /**
     * @ORM\Column(name="body_shape", type="array", nullable=true)
     * @Assert\All({
     *      @Assert\Type(
     *          type="string",
     *          message="common.basic.string"
     *      ),
     *      @Assert\Choice(
     *          choices = {
     *              UserPersonal::BODY_SHAPE_SKINNY,
     *              UserPersonal::BODY_SHAPE_AVERAGE,
     *              UserPersonal::BODY_SHAPE_SPORT,
     *              UserPersonal::BODY_SHAPE_MUSCULAR,
     *              UserPersonal::BODY_SHAPE_CHUBBY,
     *              UserPersonal::BODY_SHAPE_OBESE  
     *          }, 
     *          message = "common.basic.invalid_value"
     *      )
     * })
     */
    protected $bodyShape;

    /**
     * @ORM\Column(name="want_to", type="array", nullable=true)
     * @Assert\All({
     *      @Assert\Type(
     *          type="string",
     *          message="common.basic.string"
     *      ),
     *      @Assert\Choice(
     *          choices = {
     *              UserPersonal::WANT_TO_RELATIONSHIP,
     *              UserPersonal::WANT_TO_SEX,
     *              UserPersonal::WANT_TO_FRIENDSHIP
     *          }, 
     *           message = "common.basic.invalid_value"
     *      )
     * })
     */
    protected $wantTo;

    /**
     * @ORM\Column(name="searching_for", type="array", nullable=true)
     * @Assert\All({
     *      @Assert\Type(
     *          type="string",
     *          message="common.basic.string"
     *      ),
     *      @Assert\Choice(
     *          choices = {
     *              UserPersonal::SEARCHING_FOR_MAN,
     *              UserPersonal::SEARCHING_FOR_WOMAN,
     *              UserPersonal::SEARCHING_FOR_BOTH 
     *          }, 
     *          message = "common.basic.invalid_value"
     *      )
     * })
     */
    protected $searchingFor;

    /**
     * Set heightFrom
     *
     * @param integer $heightFrom
     *
     * @return UserSearch
     */
    public function setHeightFrom($heightFrom) {
        $this->heightFrom = $heightFrom;

        return $this;
    }

    /**
     * Get heightFrom
     *
     * @return integer
     */
    public function getHeightFrom() {
        return $this->heightFrom;
    }

    /**
     * Set heightTo
     *
     * @param integer $heightTo
     *
     * @return UserSearch
     */
    public function setHeightTo($heightTo) {
        $this->heightTo = $heightTo;

        return $this;
    }

    /**
     * Get heightTo
     *
     * @return integer
     */
    public function getHeightTo() {
        return $this->heightTo;
    }

    /**
     * Set weightFrom
     *
     * @param integer $weightFrom
     *
     * @return UserSearch
     */
    public function setWeightFrom($weightFrom) {
        $this->weightFrom = $weightFrom;

        return $this;
    }

    /**
     * Get weightFrom
     *
     * @return integer
     */
    public function getWeightFrom() {
        return $this->weightFrom;
    }

    /**
     * Set weightTo
     *
     * @param integer $weightTo
     *
     * @return UserSearch
     */
    public function setWeightTo($weightTo) {
        $this->weightTo = $weightTo;

        return $this;
    }

    /**
     * Get weightTo
     *
     * @return integer
     */
    public function getWeightTo() {
        return $this->weightTo;
    }

    /**
     * Set ageFrom
     *
     * @param integer $ageFrom
     *
     * @return UserSearch
     */
    public function setAgeFrom($ageFrom) {
        $this->ageFrom = $ageFrom;

        return $this;
    }

    /**
     * Get ageFrom
     *
     * @return integer
     */
    public function getAgeFrom() {
        return $this->ageFrom;
    }

    /**
     * Set ageTo
     *
     * @param integer $ageTo
     *
     * @return UserSearch
     */
    public function setAgeTo($ageTo) {
        $this->ageTo = $ageTo;

        return $this;
    }

    /**
     * Get ageTo
     *
     * @return integer
     */
    public function getAgeTo() {
        return $this->ageTo;
    }

    /**
     * Set gender
     *
     * @param array $gender
     *
     * @return UserSearch
     */
    public function setGender($gender) {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return array
     */
    public function getGender() {
        return $this->gender;
    }

    /**
     * Set address
     *
     * @param Address $address
     *
     * @return UserSearch
     */
    public function setAddress(Address $address = null) {
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
     * Set county
     *
     * @param string $county
     *
     * @return UserSearch
     */
    public function setCounty($county)
    {
        $this->county = $county;

        return $this;
    }

    /**
     * Get county
     *
     * @return string
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set hairColor
     *
     * @param array $hairColor
     *
     * @return UserSearch
     */
    public function setHairColor($hairColor)
    {
        $this->hairColor = $hairColor;

        return $this;
    }

    /**
     * Get hairColor
     *
     * @return array
     */
    public function getHairColor()
    {
        return $this->hairColor;
    }

    /**
     * Set hairLength
     *
     * @param array $hairLength
     *
     * @return UserSearch
     */
    public function setHairLength($hairLength)
    {
        $this->hairLength = $hairLength;

        return $this;
    }

    /**
     * Get hairLength
     *
     * @return array
     */
    public function getHairLength()
    {
        return $this->hairLength;
    }

    /**
     * Set eyeColor
     *
     * @param array $eyeColor
     *
     * @return UserSearch
     */
    public function setEyeColor($eyeColor)
    {
        $this->eyeColor = $eyeColor;

        return $this;
    }

    /**
     * Get eyeColor
     *
     * @return array
     */
    public function getEyeColor()
    {
        return $this->eyeColor;
    }

    /**
     * Set bodyShape
     *
     * @param array $bodyShape
     *
     * @return UserSearch
     */
    public function setBodyShape($bodyShape)
    {
        $this->bodyShape = $bodyShape;

        return $this;
    }

    /**
     * Get bodyShape
     *
     * @return array
     */
    public function getBodyShape()
    {
        return $this->bodyShape;
    }

    /**
     * Set wantTo
     *
     * @param array $wantTo
     *
     * @return UserSearch
     */
    public function setWantTo($wantTo)
    {
        $this->wantTo = $wantTo;

        return $this;
    }

    /**
     * Get wantTo
     *
     * @return array
     */
    public function getWantTo()
    {
        return $this->wantTo;
    }

    /**
     * Set searchingFor
     *
     * @param array $searchingFor
     *
     * @return UserSearch
     */
    public function setSearchingFor($searchingFor)
    {
        $this->searchingFor = $searchingFor;

        return $this;
    }

    /**
     * Get searchingFor
     *
     * @return array
     */
    public function getSearchingFor()
    {
        return $this->searchingFor;
    }
}
