<?php

namespace Core\CommonBundle\Managers;

use Core\CommonBundle\Exception\NotFoundEntityException;
use Doctrine\ORM\EntityManagerInterface;
use Core\CommonBundle\Entity\Address;
use Symfony\Component\Translation\TranslatorInterface;

class AddressManager {
  
    protected $entityManager;
    protected $translator;
    
    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator) {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }
    
    /**
     * Returns the Address with the given $id or throw exception if not found.
     * 
     * @param int $id
     * @return Address
     * @throws NotFoundEntityException
     */
    public function findOr404($id) {
        $address = $this->entityManager->getRepository('CoreCommonBundle:Address')->find($id);
        
        if (false === $address instanceof Address) {
            throw new NotFoundEntityException('common.address.not_found');
        }
        
        return $address;
    }
}
