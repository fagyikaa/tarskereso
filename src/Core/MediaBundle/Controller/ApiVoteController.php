<?php

namespace Core\MediaBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Core\CommonBundle\Exception\NotFoundEntityException;
use Core\CommonBundle\Exception\AccessDeniedException;

class ApiVoteController extends FOSRestController {

    /**
     * Checks if the image exists with $imageId and that the current user has access to vote. 
     * If the user hasn't voted on this image yet then creates a vote, if has voted then edits it or
     * if $stars is 0 then removes the previous vote (if exists). Returns Vote or error message if validation fails.
     * 
     * @param int $imageId
     * @param int $stars
     * @return json
     * @throws NotFoundEntityException
     * @throws AccessDeniedException
     * @throws EntityValidationException
     */
    public function voteAction($imageId, $stars) {
        $image = $this->get('core_media.image_manager')->getImageOr404($imageId);
        if ($image->getIsPrivate() && false === ($image->getOwner()->isFriendWith($this->getUser()))) {
            throw new AccessDeniedException('media.vote.only_friend_on_private');
        } elseif ($image->getOwner() === $this->getUser()) {
            throw new AccessDeniedException('media.vote.no_yourself');
        }

        $vote = $this->get('core_media.vote_manager')->vote($image, $stars, $this->getUser());

        $view = $this->view($vote, Response::HTTP_OK);

        return $this->handleView($view);
    }

}
