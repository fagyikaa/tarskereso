<?php

namespace Core\MediaBundle\Managers;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Core\MediaBundle\Entity\Image;
use Core\MediaBundle\Entity\Vote;
use Core\UserBundle\Entity\User;

class VoteManager {

    protected $em;
    protected $validator;

    public function __construct(EntityManagerInterface $em, ValidatorInterface $validator) {
        $this->em = $em;
        $this->validator = $validator;
    }

    /**
     * If $user has already voted on $image then loads that vote and sets it's stars to $stars
     * or creates a new Vote and adds to $image. If $stars is 0 then removes the vote.
     * 
     * @param Image $image
     * @param int $stars
     * @param User $user
     * @return Vote|string
     */
    public function vote(Image $image, $stars, User $user) {
        if ($stars > 0) {
            $vote = $this->getVoteOrCreate($image, $user);
            $vote->setStars(intval($stars));
            $errorOrNull = $this->validate($vote);
            if (false === is_null($errorOrNull)) {
                return $errorOrNull;
            }
            $this->em->persist($vote);
        } else {
            if ($image->hasVoted($user)) {
                $vote = $image->getVoteOf($user);
                $this->em->remove($vote);
            }
        }

        $this->em->flush();
        return $vote;
    }

    /**
     * If $user has already voted on $image then returns it, if not then creates a new
     * Vote and returns that.
     * 
     * @param Image $image
     * @param User $user
     * @return Vote
     */
    private function getVoteOrCreate($image, $user) {
        if ($image->hasVoted($user)) {
            return $image->getVoteOf($user);
        }

        $vote = new Vote();
        $vote->setImage($image);
        $vote->setUser($user);
        return $vote;
    }

    /**
     * Validates $vote and returns error message if the validation fails, returns null otherwise.
     * 
     * @param Vote $vote
     * @return string|vote
     */
    private function validate($vote) {
        $violationList = $this->validator->validate($vote);
        if (count($violationList) > 0) {
            $errors = array();
            foreach ($violationList as $violation) {
                $errors[] = $violation->getMessage();
            }
            return implode(' ', $errors);
        }

        return null;
    }

}
