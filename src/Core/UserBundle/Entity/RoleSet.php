<?php

namespace Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Core\UserBundle\Validator\Constraints as UserAssert;

/**
 * @ORM\Entity(repositoryClass="Core\UserBundle\Repository\RoleSetRepository")
 * @UserAssert\UniqueRoleSet()
 * @ORM\Table(name="role_sets")
 */
class RoleSet {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, unique=false)
     * @Assert\NotBlank(message="user.role_set.name.not_blank")
     * @Assert\Length(max=255, maxMessage="common.basic.too_long")
     * @Assert\Type(
     *      type="string",
     *      message="user.basic.type.string"
     * )
     * 
     * @JMS\Expose
     */
    protected $name;

    /**
     * @ORM\Column(type="array")
     * @Assert\Count(
     *      min = "2",
     *      minMessage = "user.role_set.roles.min",
     * )
     * 
     * @JMS\Expose
     */
    protected $roles;

    /**
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     * @Assert\DateTime(message="common.basic.type.date")
     * 
     * @JMS\Expose
     */
    protected $deletedAt;

    public function __construct() {
        $this->roles = array();
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
     * Set name
     *
     * @param string $name
     * @return RoleSet
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
     * Set roles
     *
     * @param array $roles
     * @return RoleSet
     */
    public function setRoles(Array $roles) {
        $this->roles = array_unique($roles);

        return $this;
    }

    /**
     * Get roles
     *
     * @return array 
     */
    public function getRoles() {
        $roles = $this->roles;

        return array_unique($roles);
    }

    /**
     * Add to the role set the specified role
     * 
     * @param string $role
     * @return RoleSet
     */
    public function addRole($role) {
        $role = strtoupper($role);

        if (false === in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    /**
     * Remove all roles of the role set
     * 
     * @return RoleSet
     */
    public function removeRoles() {
        $this->roles = array();

        return $this;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return RoleSet
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
     * Returns true if this RoleSet has been deleted.
     * 
     * @return boolean
     */
    public function isDeleted() {
        return !is_null($this->getDeletedAt());
    }
}
