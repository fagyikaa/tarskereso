<?php

namespace Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"introduction" = "UserIntroduction", "ideal" = "UserIdeal"})
 */
abstract class UserPersonal {

    const WANT_TO_RELATIONSHIP = 'relationship';
    const WANT_TO_SEX = 'sex';
    const WANT_TO_FRIENDSHIP = 'friendship';
    const SEARCHING_FOR_MAN = 'male';
    const SEARCHING_FOR_WOMAN = 'female';
    const SEARCHING_FOR_BOTH = 'both';
    const EYE_COLOR_GREEN = 'green';
    const EYE_COLOR_BLUE = 'blue';
    const EYE_COLOR_BROWN = 'brown';
    const EYE_COLOR_OTHER = 'other';
    const HAIR_COLOR_BLACK = 'black';
    const HAIR_COLOR_BROWN = 'brown';
    const HAIR_COLOR_BLOND = 'blond';
    const HAIR_COLOR_GRAY = 'gray';
    const HAIR_COLOR_RED = 'red';
    const HAIR_COLOR_GREEN = 'green';
    const HAIR_COLOR_BLLUE = 'blue';
    const HAIR_COLOR_PINK = 'pink';
    const HAIR_COLOR_OTHER = 'other';
    const HAIR_LENGTH_BALD = 'bald';
    const HAIR_LENGTH_SHORT = 'short';
    const HAIR_LENGTH_MIDDLE = 'middle';
    const HAIR_LENGTH_LONG = 'long';
    const BODY_SHAPE_SKINNY = 'skinny';
    const BODY_SHAPE_AVERAGE = 'average';
    const BODY_SHAPE_SPORT = 'sport';
    const BODY_SHAPE_MUSCULAR = 'muscular';
    const BODY_SHAPE_CHUBBY = 'chubby';
    const BODY_SHAPE_OBESE = 'obese';
    //Fields
    const FIELD_HAIR_COLOR = 'hairColor';
    const FIELD_HAIR_LENGTH = 'hairLength';
    const FIELD_EYE_COLOR = 'eyeColor';
    const FIELD_BODY_SHAPE = 'bodyShape';
    const FIELD_WANT_TO = 'wantTo';
    const FIELD_SEARCHING_FOR = 'searchingFor';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(name="hair_color", type="string", length=256, nullable=true)
     * @Assert\Type(
     *      type="string",
     *      message="common.basic.string"
     * )
     * @Assert\Choice(
     *     choices = {
     *         UserPersonal::HAIR_COLOR_BLACK,
     *         UserPersonal::HAIR_COLOR_BROWN,
     *         UserPersonal::HAIR_COLOR_BLOND,
     *         UserPersonal::HAIR_COLOR_GRAY,
     *         UserPersonal::HAIR_COLOR_RED,
     *         UserPersonal::HAIR_COLOR_GREEN,  
     *         UserPersonal::HAIR_COLOR_BLLUE,
     *         UserPersonal::HAIR_COLOR_PINK,
     *         UserPersonal::HAIR_COLOR_OTHER
     *     }, 
     *     message = "common.basic.invalid_value"
     * )
     */
    protected $hairColor;

    /**
     * @ORM\Column(name="hair_length", type="string", length=256, nullable=true)
     * @Assert\Type(
     *      type="string",
     *      message="common.basic.string"
     * )
     * @Assert\Choice(
     *     choices = {
     *         UserPersonal::HAIR_LENGTH_BALD,
     *         UserPersonal::HAIR_LENGTH_SHORT,
     *         UserPersonal::HAIR_LENGTH_MIDDLE,
     *         UserPersonal::HAIR_LENGTH_LONG 
     *     }, 
     *     message = "common.basic.invalid_value"
     * )
     */
    protected $hairLength;

    /**
     * @ORM\Column(name="eye_color", type="string", length=256, nullable=true)
     * @Assert\Type(
     *      type="string",
     *      message="common.basic.string"
     * )
     * @Assert\Choice(
     *     choices = {
     *         UserPersonal::EYE_COLOR_GREEN,
     *         UserPersonal::EYE_COLOR_BLUE,
     *         UserPersonal::EYE_COLOR_BROWN,
     *         UserPersonal::EYE_COLOR_OTHER  
     *     }, 
     *     message = "common.basic.invalid_value"
     * )
     */
    protected $eyeColor;

    /**
     * @ORM\Column(name="body_shape", type="string", length=256, nullable=true)
     * @Assert\Type(
     *      type="string",
     *      message="common.basic.string"
     * )
     * @Assert\Choice(
     *     choices = {
     *         UserPersonal::BODY_SHAPE_SKINNY,
     *         UserPersonal::BODY_SHAPE_AVERAGE,
     *         UserPersonal::BODY_SHAPE_SPORT,
     *         UserPersonal::BODY_SHAPE_MUSCULAR,
     *         UserPersonal::BODY_SHAPE_CHUBBY,
     *         UserPersonal::BODY_SHAPE_OBESE  
     *     }, 
     *     message = "common.basic.invalid_value"
     * )
     */
    protected $bodyShape;

    /**
     * @ORM\Column(name="want_to", type="string", length=256, nullable=true)
     * @Assert\Type(
     *      type="string",
     *      message="common.basic.string"
     * )
     * @Assert\Choice(
     *     choices = {
     *         UserPersonal::WANT_TO_RELATIONSHIP,
     *         UserPersonal::WANT_TO_SEX,
     *         UserPersonal::WANT_TO_FRIENDSHIP 
     *     }, 
     *     message = "common.basic.invalid_value"
     * )
     */
    protected $wantTo;

    /**
     * @ORM\Column(name="searching_for", type="string", length=256, nullable=true)
     * @Assert\Type(
     *      type="string",
     *      message="common.basic.string"
     * )
     * @Assert\Choice(
     *     choices = {
     *         UserPersonal::SEARCHING_FOR_MAN,
     *         UserPersonal::SEARCHING_FOR_WOMAN,
     *         UserPersonal::SEARCHING_FOR_BOTH 
     *     }, 
     *     message = "common.basic.invalid_value"
     * )
     *
     */
    protected $searchingFor;

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set hairColor
     *
     * @param string $hairColor
     *
     * @return UserPersonal
     */
    public function setHairColor($hairColor) {
        $this->hairColor = $hairColor;

        return $this;
    }

    /**
     * Get hairColor
     *
     * @return string
     */
    public function getHairColor() {
        return $this->hairColor;
    }

    /**
     * Set hairLength
     *
     * @param string $hairLength
     *
     * @return UserPersonal
     */
    public function setHairLength($hairLength) {
        $this->hairLength = $hairLength;

        return $this;
    }

    /**
     * Get hairLength
     *
     * @return string
     */
    public function getHairLength() {
        return $this->hairLength;
    }

    /**
     * Set eyeColor
     *
     * @param string $eyeColor
     *
     * @return UserPersonal
     */
    public function setEyeColor($eyeColor) {
        $this->eyeColor = $eyeColor;

        return $this;
    }

    /**
     * Get eyeColor
     *
     * @return string
     */
    public function getEyeColor() {
        return $this->eyeColor;
    }

    /**
     * Set bodyShape
     *
     * @param string $bodyShape
     *
     * @return UserPersonal
     */
    public function setBodyShape($bodyShape) {
        $this->bodyShape = $bodyShape;

        return $this;
    }

    /**
     * Get bodyShape
     *
     * @return string
     */
    public function getBodyShape() {
        return $this->bodyShape;
    }

    /**
     * Set wantTo
     *
     * @param string $wantTo
     *
     * @return UserPersonal
     */
    public function setWantTo($wantTo) {
        $this->wantTo = $wantTo;

        return $this;
    }

    /**
     * Get wantTo
     *
     * @return string
     */
    public function getWantTo() {
        return $this->wantTo;
    }

    /**
     * Set searchingFor
     *
     * @param string $searchingFor
     *
     * @return UserPersonal
     */
    public function setSearchingFor($searchingFor) {
        $this->searchingFor = $searchingFor;

        return $this;
    }

    /**
     * Get searchingFor
     *
     * @return string
     */
    public function getSearchingFor() {
        return $this->searchingFor;
    }

}
