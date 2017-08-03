<?php

namespace Core\UserBundle\Managers;

use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Core\CommonBundle\Exception\NotFoundEntityException;
use Core\UserBundle\Exception\InvalidCategoryException;
use Core\CommonBundle\Exception\EntityAlreadyDeletedException;
use Core\CommonBundle\Exception\NotFoundPropertyException;
use Core\CommonBundle\Exception\BadRequestException;
use Core\CommonBundle\Exception\EntityValidationException;
use Core\UserBundle\Entity\User;
use Core\UserBundle\Entity\UserIntroduction;
use Core\UserBundle\Entity\UserPersonal;
use Core\UserBundle\Entity\UserIdeal;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class UserManager extends BaseUserManager {

    const PROFILE_CATEGORY_INTRODUCTION = 'introduction';
    const PROFILE_CATEGORY_IDEAL = 'ideal';
    const PROFILE_FIELD_TYPE_TEXT = 'text';
    const PROFILE_FIELD_TYPE_TEXTAREA = 'textarea';
    const PROFILE_FIELD_TYPE_NUMBER = 'number';
    const PROFILE_FIELD_TYPE_RANGE = 'range';
    const PROFILE_FIELD_TYPE_CHECKBOX = 'checkbox';
    const PROFILE_FIELD_TYPE_SELECT = 'select';
    const PROFILE_FIELD_TYPE_DATE = 'date';

    protected $container;
    protected $entityManager;

    public function __construct(PasswordUpdaterInterface $passwordUpdater, CanonicalFieldsUpdater $canonicalFieldsUpdater, EntityManagerInterface $entityManager, $class, ContainerInterface $container) {
        parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $entityManager, $class);
        $this->container = $container;
        $this->entityManager = $entityManager;
    }

    /**
     * Returns an empty user instance
     *
     * @return User
     */
    public function createUser() {
        $user = new User();
        $user->setUserIntroduction(new UserIntroduction());
        $user->setUserIdeal(new UserIdeal());

        return $user;
    }

    /**
     * Returns the User with the given $userId or throws exception if no user exists with $userId.
     * 
     * @param int $userId
     * @return User
     * @throws NotFoundEntityException
     */
    public function getUserOr404($userId) {
        $user = $this->findUserBy(array('id' => $userId));
        if (false === $user instanceof User || false === is_null($user->getDeletedAt())) {
            throw new NotFoundEntityException('user.not_found_user');
        }

        return $user;
    }

    /**
     * If the give $user is already deleted then throws exception, set to now
     * the deletedAt property otherwise.
     * 
     * @param User $user
     * @return User
     * @throws EntityAlreadyDeletedException
     */
    public function setUserAsDeleted(User $user) {
        if (false === is_null($user->getDeletedAt())) {
            throw new EntityAlreadyDeletedException('user.already_deleted');
        }

        $user->setDeletedAt(new \DateTime());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Returns the user which fit the given $criteria and it's deletedAt field is null or null if no user was found. 
     * 
     * @param array $criteria
     * @return User | null
     */
    public function findUserBy(array $criteria) {
        return parent::findUserBy(array_merge($criteria, array('deletedAt' => null)));
    }

    /**
     * Returns the given user's fields and related datas according to $category which determines which data group
     * has to be returned (e.g.: introduction, ideal). If $loggedInUser is not the same as the one with $userId
     * or doesn't have the role ROLE_ADMIN_CAN_EDIT_USER_PROFILE then readOnly property will be true to indicate
     * that the field can't be edited.
     * 
     * @param int $userId
     * @param string $category
     * @param User $loggedInUser
     * @return array
     * @throws NotFoundEntityException If no user was found with $userId
     * @throws InvalidCategoryException If $category is not a PROFILE_CATEGORY_ constant
     */
    public function getProfileData($userId, $category, User $loggedInUser) {
        $user = $this->getUserOr404($userId);

        $readOnly = true;
        if ($user === $loggedInUser || $this->container->get('core_user.role_manager')->hasRole($loggedInUser, 'ROLE_ADMIN_CAN_EDIT_USER_PROFILE')) {
            $readOnly = false;
        }

        switch ($category) {
            case self::PROFILE_CATEGORY_INTRODUCTION:
                $fieldsData = $this->getProfileIntroductionFieldsData($user);
                break;
            case self::PROFILE_CATEGORY_IDEAL:
                $fieldsData = $this->getProfileIdealFieldsData($user);
                break;
            default:
                throw new InvalidCategoryException('user.invalid_profile_category');
        }

        return array(
            'readOnly' => $readOnly,
            'fieldsData' => $fieldsData,
        );
    }

    /**
     * Edits the given $user's $property to $data in the user's related entity which is determined according to $category
     * then validates that property. If it's valid then returns true, returns the error message otherwise.
     * 
     * @param User $user
     * @param string $category
     * @param string $property
     * @param string $data
     * @return boolean|string
     * @throws InvalidCategoryException If $category is not a UserManager::PROFILE_CATEGORY_ constant
     * @throws NotFoundPropertyException If $property is not a valid property in the related entity
     * @throws NotFoundEntityException If in case of address editing Address was not found
     * @throws EntityValidationException In case of validation error
     */
    public function editProfileData(User $user, $category, $property, $data) {
        switch ($category) {
            case self::PROFILE_CATEGORY_INTRODUCTION:
                $violationList = $this->setProfileIntroductionData($user, $property, $data);
                break;
            case self::PROFILE_CATEGORY_IDEAL:
                $violationList = $this->setProfileIdealData($user->getUserIdeal(), $property, $data);
                break;
            default:
                throw new InvalidCategoryException('user.invalid_profile_category');
        }

        if ($violationList->count() > 0) {
            throw new EntityValidationException($violationList);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Edits $user's email property to $email (and canonicalEmail) and if the given
     * $email is not unique then returns error message, returns $user otherwise.
     * 
     * @param User $user
     * @param string $email
     * @return User
     * @throws BadRequestException
     * @throws EntityValidationException
     */
    public function editUserEmail(User $user, $email) {
        try {
            $user->setEmail($email);
            parent::updateUser($user, false);

            $violationList = $this->container->get('validator')->validateProperty($user, 'email', array('Profile'));
            if ($violationList->count() > 0) {
                throw new EntityValidationException($violationList);
            }

            $this->entityManager->flush();

            return $user;
        } catch (UniqueConstraintViolationException $exception) {
            throw new BadRequestException('user.unique_email');
        }
    }

    /**
     * Returns the possible select values for the given $category (e.g.: introduction, ideal). The keys of the returned objects are the fields
     * which are editable through selection, 'text' holds the translations and 'value' the corresponding possible values values for each field.
     * 
     * @param string $category
     * @return array
     * @throws InvalidCategoryException If $category is not a UserManager::PROFILE_CATEGORY_ constant
     */
    public function getPossibleSelectValuesForProfileDatas($category) {
        switch ($category) {
            case self::PROFILE_CATEGORY_INTRODUCTION:
                return $this->getPossibleSelectValuesForUserIntroduction();
            case self::PROFILE_CATEGORY_IDEAL:
                return $this->getPossibleSelectValuesForUserIdeal();
            default:
                throw new InvalidCategoryException('user.invalid_profile_category');
        }
    }

    /**
     * Sets $user's enabled property to $isEnabled and validates it.
     * In case of error the error message si returned, $user returned otherwise.
     * 
     * @param User $user
     * @param boolean $isEnabled
     * @return User
     * @throws EntityValidationException
     */
    public function setUserEnabled(User $user, $isEnabled) {
        $user->setEnabled($isEnabled);

        $violationList = $this->container->get('validator')->validateProperty($user, 'enabled');
        if ($violationList->count() > 0) {
            throw new EntityValidationException($violationList);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Returns every registered (not deleted) users include admins in the format dataTables requires.
     * 
     * @param array $parameters
     * @return array
     */
    public function getAllUsersDataTable(array $parameters) {
        return $this->entityManager->getRepository('CoreUserBundle:User')->getAllUsersDataTable($parameters);
    }

    /**
     * Set the $userIntroduction's $property to $data if $property is a valid property then
     * validates it. 
     * 
     * @param User $user
     * @param string $property
     * @param string $data
     * @return ConstraintViolationList
     * @throws NotFoundPropertyException
     */
    private function setProfileIntroductionData($user, $property, $data) {
        $userIntroduction = $user->getUserIntroduction();
        switch ($property) {
            case UserPersonal::FIELD_BODY_SHAPE:
                $userIntroduction->setBodyShape($data);
                break;
            case UserPersonal::FIELD_EYE_COLOR:
                $userIntroduction->setEyeColor($data);
                break;
            case UserPersonal::FIELD_HAIR_COLOR:
                $userIntroduction->setHairColor($data);
                break;
            case UserPersonal::FIELD_HAIR_LENGTH:
                $userIntroduction->setHairLength($data);
                break;
            case UserIntroduction::FIELD_HEIGHT:
                $userIntroduction->setHeight($data);
                break;
            case UserIntroduction::FIELD_INTRODUTION:
                $userIntroduction->setIntroduction($data);
                break;
            case UserIntroduction::FIELD_MOTTO:
                $userIntroduction->setMotto($data);
                break;
            case UserPersonal::FIELD_SEARCHING_FOR:
                $userIntroduction->setSearchingFor($data);
                break;
            case UserPersonal::FIELD_WANT_TO:
                $userIntroduction->setWantTo($data);
                break;
            case UserIntroduction::FIELD_WEIGHT:
                $userIntroduction->setWeight($data);
                break;
            case User::FIELD_BIRTH_DATE:
                $user->setBirthDate(new \DateTime($data));
                return $this->container->get('validator')->validateProperty($user, $property);
            case UserIdeal::FIELD_ADDRESS:
                $address = $this->container->get('core_common.address_manager')->findOr404($data);
                $user->setAddress($address);
                return $this->container->get('validator')->validateProperty($user, $property);
            default:
                throw new NotFoundPropertyException('user.invalid_property');
        }

        return $this->container->get('validator')->validateProperty($userIntroduction, $property);
    }

    /**
     * Set the $userIdeal's $property to $data if $property is a valid property, then validates it. 
     * 
     * @param UserIdeal $userIdeal
     * @param string $property
     * @param string $data
     * @return ConstraintViolationList
     * @throws NotFoundPropertyException
     * @throws NotFoundEntityException
     */
    private function setProfileIdealData($userIdeal, $property, $data) {
        switch ($property) {
            case UserPersonal::FIELD_BODY_SHAPE:
                $userIdeal->setBodyShape($data);
                break;
            case UserPersonal::FIELD_EYE_COLOR:
                $userIdeal->setEyeColor($data);
                break;
            case UserPersonal::FIELD_HAIR_COLOR:
                $userIdeal->setHairColor($data);
                break;
            case UserPersonal::FIELD_HAIR_LENGTH:
                $userIdeal->setHairLength($data);
                break;
            case UserIdeal::FIELD_HEIGHT_FROM:
                $userIdeal->setHeightFrom($data);
                break;
            case UserIdeal::FIELD_HEIGHT_TO:
                $userIdeal->setHeightTo($data);
                break;
            case UserPersonal::FIELD_SEARCHING_FOR:
                $userIdeal->setSearchingFor($data);
                break;
            case UserPersonal::FIELD_WANT_TO:
                $userIdeal->setWantTo($data);
                break;
            case UserIdeal::FIELD_WEIGHT_FROM:
                $userIdeal->setWeightFrom($data);
                break;
            case UserIdeal::FIELD_WEIGHT_TO:
                $userIdeal->setWeightTo($data);
                break;
            case UserIdeal::FIELD_AGE_FROM:
                $userIdeal->setAgeFrom($data);
                break;
            case UserIdeal::FIELD_AGE_TO:
                $userIdeal->setAgeTo($data);
                break;
            case UserIdeal::FIELD_GENDER:
                $userIdeal->setGender($data);
                break;
            case UserIdeal::FIELD_ADDRESS:
                $address = $this->container->get('core_common.address_manager')->findOr404($data);
                $userIdeal->setAddress($address);
                break;
            default:
                throw new NotFoundPropertyException('user.invalid_property');
        }

        return $this->container->get('validator')->validateProperty($userIdeal, $property);
    }

    /**
     * Returns the detailed data of the given user's introduction. Each field's data is
     * under the field's translation. 'data' holds the data, 'type' the field type
     * 'property' the property name and if type is numeric then 'min' and 'max' the assertions.
     * 
     * @param User $user
     * @return array
     */
    private function getProfileIntroductionFieldsData($user) {
        $translator = $this->container->get('translator');
        $userIntroduction = $user->getUserIntroduction();

        return array(
            $translator->trans('introduction.field_titles.body_shape', array(), 'profile') => array(
                'data' => $userIntroduction->getBodyShape(),
                'type' => self::PROFILE_FIELD_TYPE_SELECT,
                'property' => UserPersonal::FIELD_BODY_SHAPE,
            ),
            $translator->trans('introduction.field_titles.eye_color', array(), 'profile') => array(
                'data' => $userIntroduction->getEyeColor(),
                'type' => self::PROFILE_FIELD_TYPE_SELECT,
                'property' => UserPersonal::FIELD_EYE_COLOR,
            ),
            $translator->trans('introduction.field_titles.hair_color', array(), 'profile') => array(
                'data' => $userIntroduction->getHairColor(),
                'type' => self::PROFILE_FIELD_TYPE_SELECT,
                'property' => UserPersonal::FIELD_HAIR_COLOR,
            ),
            $translator->trans('introduction.field_titles.hair_length', array(), 'profile') => array(
                'data' => $userIntroduction->getHairLength(),
                'type' => self::PROFILE_FIELD_TYPE_SELECT,
                'property' => UserPersonal::FIELD_HAIR_LENGTH,
            ),
            $translator->trans('introduction.field_titles.height', array(), 'profile') => array(
                'data' => $userIntroduction->getHeight(),
                'type' => self::PROFILE_FIELD_TYPE_NUMBER,
                'property' => UserIntroduction::FIELD_HEIGHT,
                'min' => 50,
                'max' => 300,
            ),
            $translator->trans('introduction.field_titles.introduction', array(), 'profile') => array(
                'data' => $userIntroduction->getIntroduction(),
                'type' => self::PROFILE_FIELD_TYPE_TEXTAREA,
                'property' => UserIntroduction::FIELD_INTRODUTION,
            ),
            $translator->trans('introduction.field_titles.motto', array(), 'profile') => array(
                'data' => $userIntroduction->getMotto(),
                'type' => self::PROFILE_FIELD_TYPE_TEXTAREA,
                'property' => UserIntroduction::FIELD_MOTTO,
            ),
            $translator->trans('introduction.field_titles.searching_for', array(), 'profile') => array(
                'data' => $userIntroduction->getSearchingFor(),
                'type' => self::PROFILE_FIELD_TYPE_SELECT,
                'property' => UserPersonal::FIELD_SEARCHING_FOR,
            ),
            $translator->trans('introduction.field_titles.want_to', array(), 'profile') => array(
                'data' => $userIntroduction->getWantTo(),
                'type' => self::PROFILE_FIELD_TYPE_SELECT,
                'property' => UserPersonal::FIELD_WANT_TO,
            ),
            $translator->trans('introduction.field_titles.birth_date', array(), 'profile') => array(
                'data' => $user->getBirthDate()->format('Y-m-d'),
                'max' => (date('Y') - 18),
                'min' => (date('Y') - 120),
                'type' => self::PROFILE_FIELD_TYPE_DATE,
                'property' => User::FIELD_BIRTH_DATE,
            ),
            $translator->trans('introduction.field_titles.weight', array(), 'profile') => array(
                'data' => $userIntroduction->getWeight(),
                'type' => self::PROFILE_FIELD_TYPE_NUMBER,
                'property' => UserIntroduction::FIELD_WEIGHT,
                'min' => 30,
                'max' => 400,
            ),
            $translator->trans('ideal.field_titles.address', array(), 'profile') => array(
                'data' => $user->getAddress()->__toString(),
                'type' => self::PROFILE_FIELD_TYPE_SELECT,
                'property' => UserIdeal::FIELD_ADDRESS,
            )
        );
    }

    /**
     * Returns the detailed data of the given user's ideal. Each field's data is
     * under the field's translation. 'data' holds the data, 'type' the field type
     * 'property' the property name and if type is numeric then 'min' and 'max' the assertions.
     * If it's range then data_from/to, property_from/to correspondingly.
     * 
     * @param User $user
     * @return array
     */
    private function getProfileIdealFieldsData($user) {
        $translator = $this->container->get('translator');
        $userIdeal = $user->getUserIdeal();

        return array(
            $translator->trans('ideal.field_titles.body_shape', array(), 'profile') => array(
                'data' => $userIdeal->getBodyShape(),
                'type' => self::PROFILE_FIELD_TYPE_SELECT,
                'property' => UserPersonal::FIELD_BODY_SHAPE,
            ),
            $translator->trans('ideal.field_titles.eye_color', array(), 'profile') => array(
                'data' => $userIdeal->getEyeColor(),
                'type' => self::PROFILE_FIELD_TYPE_SELECT,
                'property' => UserPersonal::FIELD_EYE_COLOR,
            ),
            $translator->trans('ideal.field_titles.hair_color', array(), 'profile') => array(
                'data' => $userIdeal->getHairColor(),
                'type' => self::PROFILE_FIELD_TYPE_SELECT,
                'property' => UserPersonal::FIELD_HAIR_COLOR,
            ),
            $translator->trans('ideal.field_titles.hair_length', array(), 'profile') => array(
                'data' => $userIdeal->getHairLength(),
                'type' => self::PROFILE_FIELD_TYPE_SELECT,
                'property' => UserPersonal::FIELD_HAIR_LENGTH,
            ),
            $translator->trans('ideal.field_titles.height', array(), 'profile') => array(
                'data_from' => $userIdeal->getHeightFrom(),
                'data_to' => $userIdeal->getHeightTo(),
                'type' => self::PROFILE_FIELD_TYPE_RANGE,
                'property_from' => UserIdeal::FIELD_HEIGHT_FROM,
                'property_to' => UserIdeal::FIELD_HEIGHT_TO,
                'min' => 50,
                'max' => 300,
            ),
            $translator->trans('ideal.field_titles.weight', array(), 'profile') => array(
                'data_from' => $userIdeal->getWeightFrom(),
                'data_to' => $userIdeal->getWeightTo(),
                'type' => self::PROFILE_FIELD_TYPE_RANGE,
                'property_from' => UserIdeal::FIELD_WEIGHT_FROM,
                'property_to' => UserIdeal::FIELD_WEIGHT_TO,
                'min' => 30,
                'max' => 400,
            ),
            $translator->trans('ideal.field_titles.age', array(), 'profile') => array(
                'data_from' => $userIdeal->getAgeFrom(),
                'data_to' => $userIdeal->getAgeTo(),
                'type' => self::PROFILE_FIELD_TYPE_RANGE,
                'property_from' => UserIdeal::FIELD_AGE_FROM,
                'property_to' => UserIdeal::FIELD_AGE_TO,
                'min' => 18,
                'max' => 100,
            ),
            $translator->trans('ideal.field_titles.gender', array(), 'profile') => array(
                'data' => $userIdeal->getGender(),
                'type' => self::PROFILE_FIELD_TYPE_SELECT,
                'property' => UserIdeal::FIELD_GENDER,
            ),
            $translator->trans('ideal.field_titles.want_to', array(), 'profile') => array(
                'data' => $userIdeal->getWantTo(),
                'type' => self::PROFILE_FIELD_TYPE_SELECT,
                'property' => UserPersonal::FIELD_WANT_TO,
            ),
            $translator->trans('ideal.field_titles.searching_for', array(), 'profile') => array(
                'data' => $userIdeal->getSearchingFor(),
                'type' => self::PROFILE_FIELD_TYPE_SELECT,
                'property' => UserPersonal::FIELD_SEARCHING_FOR,
            ),
            $translator->trans('ideal.field_titles.address', array(), 'profile') => array(
                'data' => $userIdeal->getAddress()->__toString(),
                'type' => self::PROFILE_FIELD_TYPE_SELECT,
                'property' => UserIdeal::FIELD_ADDRESS,
            ),
        );
    }

    /**
     * Returns every possible select values grouped by the fields which can be edited through selection for
     * UserIntroduction entity. 'text' holds the translations, 'value' holds the possible values for each field.
     * 
     * @return array
     */
    private function getPossibleSelectValuesForUserIntroduction() {
        $selectableFields = $this->getPossibleSelectValuesForUserPersonal();
        $selectableFields[UserIdeal::FIELD_ADDRESS] = $this->getPossibleSelectValuesForAddress();

        return $selectableFields;
    }

    /**
     * Returns every possible select values grouped by the fields which can be edited through selection for
     * UserIdeal entity. 'text' holds the translations, 'value' holds the possible values for each field.
     * 
     * @return array
     */
    private function getPossibleSelectValuesForUserIdeal() {
        $translator = $this->container->get('translator');

        $selectableFields = $this->getPossibleSelectValuesForUserPersonal();
        $selectableFields[UserIdeal::FIELD_ADDRESS] = $this->getPossibleSelectValuesForAddress();
        $selectableFields[UserIdeal::FIELD_GENDER] = array(
            array(
                'text' => $translator->trans('ideal.gender.male', array(), 'profile'),
                'value' => User::GENDER_MALE,
            ),
            array(
                'text' => $translator->trans('ideal.gender.female', array(), 'profile'),
                'value' => User::GENDER_FEMALE,
            ),
        );

        return $selectableFields;
    }

    /**
     * Returns in an associative array every Address' settlement as 'text' and 'value' and id as 'id'.
     * 
     * @return array
     */
    private function getPossibleSelectValuesForAddress() {
        $selectableFields = array();
        foreach ($this->entityManager->getRepository('CoreCommonBundle:Address')->getNameAndIdForAll() as $address) {
            $selectableFields[] = array(
                'text' => $address['settlement'],
                'value' => $address['settlement'],
                'id' => $address['id'],
            );
        }

        return $selectableFields;
    }

    /**
     * Returns every possible select values grouped by the fields which can be edited through selection for
     * UserPersonal entity. 'text' holds the translations, 'value' holds the possible values for each field.
     * 
     * @return array
     */
    private function getPossibleSelectValuesForUserPersonal() {
        $translator = $this->container->get('translator');
        $constants = (new \ReflectionClass('Core\UserBundle\Entity\UserPersonal'))->getConstants();
        $selectableFields = array(
            UserPersonal::FIELD_BODY_SHAPE => array(),
            UserPersonal::FIELD_EYE_COLOR => array(),
            UserPersonal::FIELD_HAIR_COLOR => array(),
            UserPersonal::FIELD_HAIR_LENGTH => array(),
            UserPersonal::FIELD_SEARCHING_FOR => array(),
            UserPersonal::FIELD_WANT_TO => array(),
        );

        foreach ($constants as $name => $value) {
            if (strpos($name, 'BODY_SHAPE') !== false && strpos($name, 'FIELD') === false) {
                $selectableFields[UserPersonal::FIELD_BODY_SHAPE][] = array(
                    'text' => $translator->trans('personal.body_shape.' . $this->camelCaseToUnderScore($value), array(), 'profile'),
                    'value' => $value,
                );
            } elseif (strpos($name, 'EYE_COLOR') !== false && strpos($name, 'FIELD') === false) {
                $selectableFields[UserPersonal::FIELD_EYE_COLOR][] = array(
                    'text' => $translator->trans('personal.eye_color.' . $this->camelCaseToUnderScore($value), array(), 'profile'),
                    'value' => $value,
                );
            } elseif (strpos($name, 'HAIR_COLOR') !== false && strpos($name, 'FIELD') === false) {
                $selectableFields[UserPersonal::FIELD_HAIR_COLOR][] = array(
                    'text' => $translator->trans('personal.hair_color.' . $this->camelCaseToUnderScore($value), array(), 'profile'),
                    'value' => $value,
                );
            } elseif (strpos($name, 'HAIR_LENGTH') !== false && strpos($name, 'FIELD') === false) {
                $selectableFields[UserPersonal::FIELD_HAIR_LENGTH][] = array(
                    'text' => $translator->trans('personal.hair_length.' . $this->camelCaseToUnderScore($value), array(), 'profile'),
                    'value' => $value,
                );
            } elseif (strpos($name, 'SEARCHING_FOR') !== false && strpos($name, 'FIELD') === false) {
                $selectableFields[UserPersonal::FIELD_SEARCHING_FOR][] = array(
                    'text' => $translator->trans('personal.searching_for.' . $this->camelCaseToUnderScore($value), array(), 'profile'),
                    'value' => $value,
                );
            } elseif (strpos($name, 'WANT_TO') !== false && strpos($name, 'FIELD') === false) {
                $selectableFields[UserPersonal::FIELD_WANT_TO][] = array(
                    'text' => $translator->trans('personal.want_to.' . $this->camelCaseToUnderScore($value), array(), 'profile'),
                    'value' => $value,
                );
            }
        }

        return $selectableFields;
    }

    /**
     * Converts the camelCased string to under_scored.
     * 
     * @param string $camelCasedString
     * @return string
     */
    private function camelCaseToUnderScore($camelCasedString) {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $camelCasedString)), '_');
    }

}
