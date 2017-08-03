<?php

namespace Core\MediaBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Core\CommonBundle\Exception\AccessDeniedException;

class ImageController extends FOSRestController {

    /**
     * Renders the upload image modal if $userId is the current user's id.
     * 
     * @param int $userId
     * @param string $isProfile
     * @return html
     * @throws AccessDeniedHttpException
     */
    public function uploadImageModalAction($userId, $isProfile = 'false') {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);
        if ($user !== $this->getUser()) {
            throw new AccessDeniedException('media.upload_image.only_self');
        }

        $view = $this->view()
                ->setTemplateData(array(
                    'user' => $user,
                    'isProfile' => $isProfile
                ))
                ->setTemplate('CoreMediaBundle:Image:uploadImageModal.html.twig')
                ->setFormat('html');

        return $this->handleView($view);
    }

    /**
     * Renders the upload image form.
     * 
     * @param string $isProfile
     * @return html
     */
    public function uploadImageFormAction($isProfile = 'false') {
        $view = $this->view()
                ->setData(array(
                    'form' => $this->createForm($this->get('core_media.form.type.upload_image')),
                    'isProfile' => $isProfile,
                ))
                ->setTemplate('CoreMediaBundle:Image:uploadImageForm.html.twig')
                ->setFormat('html');

        return $this->handleView($view);
    }

    /**
     * Renders the view image modal if the image with $imageId is viewable by the current user.
     * 
     * @param int $imageId
     * @return html
     * @throws AccessDeniedHttpException
     * @throws EntityNotFoundException
     */
    public function viewImageModalAction($imageId) {
        $image = $this->get('core_media.image_manager')->getImageOr404($imageId);
        if ($image->getIsPrivate() && false === ($this->isGranted('ROLE_ADMIN_CAN_VIEW_PRIVATE_IMAGES') || $image->isViewAbleFor($this->getUser()))) {
            throw new AccessDeniedException('media.serve_image_access_denied');
        }

        $view = $this->view()
                ->setTemplateData(array(
                    'image' => $image,
                    'readOnly' => ($image->getOwner() === $this->getUser() || $this->isGranted('ROLE_ADMIN_CAN_EDIT_IMAGE')) ? 'false' : 'true',
                ))
                ->setTemplate('CoreMediaBundle:Image:viewImageModal.html.twig')
                ->setFormat('html');

        return $this->handleView($view);
    }

}
