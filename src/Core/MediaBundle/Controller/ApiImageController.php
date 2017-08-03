<?php

namespace Core\MediaBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Core\MediaBundle\Entity\Image;
use Core\CommonBundle\Exception\NotFoundEntityException;
use Core\CommonBundle\Exception\AccessDeniedException;

class ApiImageController extends FOSRestController {

    const IMAGE_SERVING_TYPE_NORMAL = 'normal';
    const IMAGE_SERVING_TYPE_THUMBNAIL = 'thumbnail';

    /**
     * Return the set of mime types that are allowed to upload and the maximum acceptable file size.
     * 
     * @return json
     */
    public function getAllowedMimeTypesAndFileSizeAction() {
        $view = $this->view(array(
            'mimeTypes' => $this->get('core_media.helper')->getAllowedMimetypes(),
            'maxFileSize' => Image::MAX_FILE_SIZE,
                ), Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * Checks if the image exists with $imageId and the current user has access to view then
     * serves the image or it's thumbnail according to $type and $size.
     * 
     * @param int $imageId
     * @param string $type
     * @param int $size
     * @return file
     * @throws NotFoundEntityException
     * @throws AccessDeniedException
     */
    public function serveImageAction($imageId, $type = self::IMAGE_SERVING_TYPE_NORMAL, $size = 150) {
        $image = $this->get('core_media.image_manager')->getImageOr404($imageId);
        if ($image->getIsPrivate() && false === ($this->isGranted('ROLE_ADMIN_CAN_VIEW_PRIVATE_IMAGES') || $image->isViewAbleFor($this->getUser()))) {
            throw new AccessDeniedException('media.serve_image.access_denied');
        }

        $params = $this->get('core_media.image_manager')->getParamsForImageServing($image, $type, $size);
        return $this->get('igorw_file_serve.response_factory')->create($params['url'], $params['format'], $params['options']);
    }

    /**
     * Checks if the image exists with $imageId and the current user has access to view then
     * serves the image's thumbnail. If $request contains size paramter then serves with that size.
     * 
     * @param Request $request
     * @param int $imageId
     * @return redirect response
     * @throws NotFoundEntityException
     * @throws AccessDeniedException
     */
    public function serveImageThumbnailAction(Request $request, $imageId) {
        $image = $this->get('core_media.image_manager')->getImageOr404($imageId);
        if ($image->getIsPrivate() && false === ($this->isGranted('ROLE_ADMIN_CAN_VIEW_PRIVATE_IMAGES') || $image->isViewAbleFor($this->getUser()))) {
            throw new AccessDeniedException('media.serve_image.access_denied');
        }

        return $this->get('core_media.image_manager')->serveImageThumbnail($image, $image->getOwner()->getId(), $request);
    }

    /**
     * Saves an uploaded image by the user. In case of success the id of the entity is returned.
     * 
     * @param Request $request
     * @param int $userId
     * @return json
     * @throws AccessDeniedException
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    public function uploadImageAction(Request $request, $userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);
        if ($user !== $this->getUser()) {
            throw new AccessDeniedException('media.upload_image.only_self');
        }

        $image = $this->get('core_media.image_manager')->createImageOr400($request->request->all()[$this->get('core_media.form.type.upload_image')->getName()], $request->files->get('file'));

        $view = $this->view($image, Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * Checks if any user exists with $userId and returns all it's public images.
     * 
     * @param int $userId
     * @return json
     * @throws EntityNotFoundException
     */
    public function getPublicImagesForUserAction($userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);
        $imagesArray = $user->getPublicImages();

        $view = $this->view($imagesArray, Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * Checks if any user exists with $userId and returns all it's private images if the current
     * user has access.
     * 
     * @param int $userId
     * @return json
     * @throws AccessDeniedException
     * @throws EntityNotFoundException
     */
    public function getPrivateImagesForUserAction($userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);
        if ($user !== $this->getUser() && false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_PRIVATE_IMAGES') && false === $user->isFriendWith($this->getUser())) {
            throw new AccessDeniedException('media.get_private_images.access_denied');
        }
        $imagesArray = $user->getPrivateImages();

        $view = $this->view($imagesArray, Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * Checks if any image exists with $imageId and if the current user is the owner
     * or an admin with ROLE_ADMIN_CAN_DELETE_IMAGE and deletes the image.
     * 
     * @param Request $request
     * @param int $imageId
     * @return json
     * @throws AccessDeniedException
     * @throws EntityNotFoundException
     */
    public function removeImageAction(Request $request, $imageId) {
        $image = $this->get('core_media.image_manager')->getImageOr404($imageId);
        if ($image->getOwner() !== $this->getUser() && false === $this->isGranted('ROLE_ADMIN_CAN_DELETE_IMAGE')) {
            throw new AccessDeniedException('media.remove.access_denied');
        }

        $this->get('core_media.image_manager')->removeImage($image);

        $view = $this->view(null, Response::HTTP_NO_CONTENT);
        return $this->handleView($view);
    }

    /**
     * Checks if any image exists with $imageId and if the current user has access to
     * view that and returns it if do.
     * 
     * @param Request $request
     * @param int $imageId
     * @return json
     * @throws AccessDeniedException
     * @throws EntityNotFoundException
     */
    public function getImageAction(Request $request, $imageId) {
        $image = $this->get('core_media.image_manager')->getImageOr404($imageId);
        if ($image->getIsPrivate() &&
                $image->getOwner() !== $this->getUser() &&
                false === $this->isGranted('ROLE_ADMIN_CAN_VIEW_PRIVATE_IMAGES') &&
                false === $image->getOwner()->isFriendWith($this->getUser())
        ) {
            throw new AccessDeniedException('media.serve_image.access_denied');
        }

        $view = $this->view(array(
            'image' => $image,
            'vote' => $image->getVoteOf($this->getUser()),
                ), Response::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Checks if any image exists with $imageId and if the current user is the owner
     * or an admin with ROLE_ADMIN_CAN_EDIT_IMAGE and then edit's the image. The edited
     * property is under the property parameter, the data is under data parameter. Returns
     * error message if validation fails.
     * 
     * @param Request $request
     * @param int $imageId
     * @return json
     * @throws AccessDeniedException
     * @throws EntityNotFoundException
     * @throws NotFoundPropertyException
     * @throws EntityValidationException
     */
    public function editImageDataAction(Request $request) {
        $image = $this->get('core_media.image_manager')->getImageOr404($request->request->get('imageId'));
        if ($image->getOwner() !== $this->getUser() && false === $this->isGranted('ROLE_ADMIN_CAN_EDIT_IMAGE')) {
            throw new AccessDeniedException('media.edit.access_denied');
        }

        $imageOrErrorMessage = $this->get('core_media.image_manager')->editImageData($image, $request->request->get('property'), $request->request->get('data'));

        if ($imageOrErrorMessage instanceof Image) {
            $view = $this->view($imageOrErrorMessage, Response::HTTP_OK);
        } else {
            $view = $this->view($imageOrErrorMessage, Response::HTTP_BAD_REQUEST);
        }

        return $this->handleView($view);
    }

}
