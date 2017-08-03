<?php

namespace Core\CommonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Core\UserBundle\Entity\User;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="Core\CommonBundle\Repository\AddressRepository")
 * @ORM\Table(name="addresses")
 * 
 * @JMS\ExclusionPolicy("all")
 */
class Address {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @JMS\Expose
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotBlank(message="user.address.not_blank")
     * @Assert\Type(type="string", message="user.address.type")
     *
     * @JMS\Expose
     */
    protected $settlement;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotBlank(message="user.address.not_blank")
     * @Assert\Type(type="string", message="user.address.type")
     *
     * @JMS\Expose
     */
    protected $county;

    /**
     * @ORM\OneToMany(targetEntity="Core\UserBundle\Entity\User", mappedBy="address")
     */
    protected $users;

    public function __construct() {
        $this->users = new ArrayCollection();
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
     * Set settlement
     *
     * @param string $settlement
     *
     * @return Address
     */
    public function setSettlement($settlement) {
        $this->settlement = $settlement;

        return $this;
    }

    /**
     * Get settlement
     *
     * @return string
     */
    public function getSettlement() {
        return $this->settlement;
    }

    /**
     * Set county
     *
     * @param string $county
     *
     * @return Address
     */
    public function setCounty($county) {
        $this->county = $county;

        return $this;
    }

    /**
     * Get county
     *
     * @return string
     */
    public function getCounty() {
        return $this->county;
    }

    /**
     * Add user
     *
     * @param User $user
     *
     * @return Address
     */
    public function addUser(User $user) {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param User $user
     */
    public function removeUser(User $user) {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers() {
        return $this->users;
    }

    /**
     * Returns the settlement of this address
     * 
     * @return string
     */
    public function __toString() {
        return $this->settlement;
    }

}
