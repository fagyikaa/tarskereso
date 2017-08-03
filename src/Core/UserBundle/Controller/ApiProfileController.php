<?php

namespace Core\UserBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\GetResponseUserEvent;
use Symfony\Component\HttpFoundation\Request;
use Core\CommonBundle\Exception\NotFoundEntityException;
use Core\CommonBundle\Exception\AccessDeniedException;
use Core\UserBundle\Form\Type\ChangePasswordType;
use Symfony\Component\HttpFoundation\Response;
use FOS\UserBundle\Event\FilterUserResponseEvent;

class ApiProfileController extends FOSRestController {

    const DEFAULT_IMAGE_PREVIEW = 'default_image_preview';

    /**
     * Returns the fields and related datas of the user with $userId according to $category which determines which data group
     * has to be returned (e.g.: introduction, ideal). If the logged in user is not the same as the one with $userId
     * or doesn't have the role ROLE_ADMIN_CAN_EDIT_USER_PROFILE then readOnly property will be true to indicate
     * that the field can't be edited.
     * 
     * @param int $userId
     * @param string $category
     * @return json
     * @throws NotFoundEntityException If no user was found with $userId
     * @throws InvalidCategoryException If $category is not a UserManager::PROFILE_CATEGORY_ constant
     */
    public function getProfileDataAction($userId, $category) {
        $datas = $this->get('core_user.user_manager')->getProfileData($userId, $category, $this->getUser());

        $view = $this->view($datas, Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * Returns the possible select values for the given $category (e.g.: introduction, ideal). The keys of the returned objects are the fields
     * which are editable through selection. 'text' holds the translations and 'value' the corresponding available values for each field.
     * 
     * @param string $category
     * @return json
     * @throws InvalidCategoryException If $category is not a UserManager::PROFILE_CATEGORY_ constant
     */
    public function getPossibleSelectValuesForProfileDatasAction($category) {
        $possibleSelectValues = $this->get('core_user.user_manager')->getPossibleSelectValuesForProfileDatas($category);

        $view = $this->view($possibleSelectValues, Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * Edits a property of the user with the userId parameter. The editing property is given with the category (which related entity of user)
     * and property parameters and set to the value of data parameter. HTTP_OK with true returned in case of success.
     * 
     * Edit's the given $user's $property to $data in the user's related entity which is determined according to $category
     * then validates that property. If it's valid then returns true, returns the error message otherwise.
     * 
     * @param Request $request
     * @return json
     * @throws InvalidCategoryException If $category is not a UserManager::PROFILE_CATEGORY_ constant
     * @throws NotFoundPropertyException If $property is not a valid property in the related entity
     * @throws NotFoundEntityException If in case of address editing Address was not found
     * @throws EntityValidationException In case of validation error
     * @throws AccessDeniedException If the current user is not equals with the one with the userId parameter and not has the role ROLE_ADMIN_CAN_EDIT_USER_PROFILE
     */
    public function editProfileDataAction(Request $request) {
        $user = $this->get('core_user.user_manager')->getUserOr404($request->request->get('userId'));
        if ($user !== $this->getUser() && false === $this->isGranted('ROLE_ADMIN_CAN_EDIT_USER_PROFILE')) {
            throw new AccessDeniedException('user.no_right_to_edit_profile');
        }

        $this->get('core_user.user_manager')->editProfileData($user, $request->request->get('category'), $request->request->get('property'), $request->request->get('data'));

        $view = $this->view(true, Response::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Edits the current user's email (and canonicalEmail) property to the email given
     * under request's email parameter. If it's not unique then returns error message, true otherwise.
     * 
     * @param Request $request
     * @return json
     * @throws BadRequestException
     * @throws EntityValidationException
     */
    public function editUserEmailAction(Request $request) {
        $this->get('core_user.user_manager')->editUserEmail($this->getUser(), $request->request->get('email'));

        $view = $this->view(true, Response::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Returns a redirect response to the user's profile image or default image according to
     * the user's gender if no image has been uploaded yet.
     * 
     * @param Request $request
     * @param int $userId
     * @return RedirectResponse
     * @throws NotFoundEntityException If no user was found with $userId parameter
     */
    public function serveProfileImageThumbnailAction(Request $request, $userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);

        $imageOrNull = $user->getProfileImage();
        if (is_null($imageOrNull)) {
            $filter = self::DEFAULT_IMAGE_PREVIEW;
            if ($request->query->has('size')) {
                $this->get('core_media.helper')->setPreviewImageSizeForServing($request->query->get('size'), $filter);
            }

            return $imagemanagerResponse = $this->container->get('liip_imagine.controller')
                    ->filterAction(
                    $request, // http request
                    'def_' . $user->getGender() . '.jpg', // original image you want to apply a filter to
                    $filter // filter defined in config.yml
            );
        } else {
            return $this->get('core_media.image_manager')->serveImageThumbnail($imageOrNull, $user->getId(), $request);
        }
    }

    /**
     * Returns the user's with $userId profile image id or 0 if the user doesn't have one.
     * 
     * @param integer $userId
     * @return json
     * @throws NotFoundEntityException If no user was found with $userId parameter
     */
    public function getProfilePictureIdAction($userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);

        $view = $this->view($user->getProfileImageId(), Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * Changes the current user's password if the given valeus are valid.
     * 
     * @param Request $request
     * @return json
     */
    public function changePasswordAction(Request $request) {
        $user = $this->getUser();

        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->get('form.factory')->create(new ChangePasswordType());
        $form->setData($user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $userManager = $this->get('core_user.user_manager');

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_SUCCESS, $event);

            $userManager->updateUser($user);

            $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_COMPLETED, new FilterUserResponseEvent($user, $request, new Response('OK')));

            $view = $this->view(array('response' => 'OK'), Response::HTTP_OK);
        } else {
            $view = $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
        }

        return $this->handleView($view);
    }

    /**
     * If the user with $userId not exists or isn't the current user and don't have 
     * ROLE_ADMIN_CAN_DELETE_USER role then throws exception. Set the user's deletedAt
     * property to now otherwise. 
     * 
     * @param int $userId
     * @return json
     * @throws AccessDeniedException
     * @throws EntityAlreadyDeletedException
     */
    public function deleteUserAction($userId) {
        $user = $this->get('core_user.user_manager')->getUserOr404($userId);
        if ($user !== $this->getUser() && false === $this->isGranted('ROLE_ADMIN_CAN_DELETE_USER')) {
            throw new AccessDeniedException('user.no_right_to_delete_user');
        }

        $this->get('core_user.user_manager')->setUserAsDeleted($user);

        $logout = $user === $this->getUser();

        $view = $this->view(array('logout' => $logout), Response::HTTP_OK);

        return $this->handleView($view);
    }

}
