<?php

namespace Core\UserBundle\Managers;

use Doctrine\ORM\EntityManagerInterface;
use Core\UserBundle\Entity\User;
use Symfony\Component\Form\FormFactoryInterface;
use Core\UserBundle\Form\Type\SearchType;
use Core\CommonBundle\Exception\InvalidFormException;
use Core\UserBundle\Entity\UserSearch;

class SearchManager {

    protected $em;
    protected $formFactory;
    protected $searchType;

    public function __construct(EntityManagerInterface $em, FormFactoryInterface $formFactory, SearchType $searchType) {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->searchType = $searchType;
    }

    /**
     * Searches for users except admins and the one with the id of $userId who are enabled, not locked,
     * not deleted and firts for the given criteries in $params. 
     * 
     * u.enabled = 1 AND u.locked = 0 AND u.deletedAt IS NULL 
     * 
     * @param int $userId
     * @param array $params
     * @return array
     */
    public function searchByFiltersExceptAdminsAndGivenOne($userId, $params) {
        $users = $this->em->getRepository('CoreUserBundle:User')->findAllByFiltersExceptAdminsAndGivenOne($userId, $params);

        return $users;
    }

    /**
     * If $user has a UserSearch then returns that, if doesn't have then returns a new one.
     * 
     * @param User $user
     * @return UserSearch
     */
    public function getUserSearchForUser(User $user) {
        $userSearch = $user->getUserSearch();
       
        if (false === $userSearch instanceof UserSearch) {
            return new UserSearch();
        }

        return $userSearch;
    }

    /**
     * Creates and validates a UserSearch from $parameters. If it's valid then sets
     * it to $user and returns it. 
     * 
     * @param User $user
     * @param array $parameters
     * @return UserSearch
     * @throws EntityValidationException
     */
    public function saveUserSearch(User $user, $parameters) {
        $form = $this->formFactory->create($this->searchType, $this->getUserSearchForUser($user));
        $form->submit($parameters);
       
        if ($form->isValid()) {
            $userSearch = $form->getData();
            $user->setUserSearch($userSearch);

            $this->em->persist($user);
            $this->em->flush();

            return $userSearch;
        }

        throw new InvalidFormException($form->getErrors(true));
    }

}
