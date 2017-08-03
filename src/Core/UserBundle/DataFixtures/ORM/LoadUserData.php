<?php

namespace Core\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Core\UserBundle\Entity\User;
//use Core\UserBundle\Entity\RoleSet;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Core\UserBundle\Entity\UserIntroduction;
use Core\UserBundle\Entity\UserIdeal;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface {

    const USER_COUNT = 500;

    private $container;
    private $addressRepository;
    private $addressCount;
    private $supportedLanguagesArray;

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
        $this->supportedLanguagesArray = $container->get('core_common.helper')->getSupportedLanguagesArray();
    }

    public function load(ObjectManager $manager) {
        $this->addressRepository = $manager->getRepository('CoreCommonBundle:Address');
        $this->addressCount = $this->addressRepository->getCountOfAddresses();
        //$this->createRoleSets($manager, 10);
        //Create users for easier logins, username is the given second parameter, password is 'password'
        $personalUser = $this->getFakeUserObject($manager, 'user1', true, false, array(), 0);
        $adminUser = $this->getFakeUserObject($manager, 'admin1', true, true, array(), 1);
        $simpleAdminUser = $this->getFakeUserObject($manager, 'simpleadmin', true, false, array('ROLE_ADMIN'), 2);
        $manager->persist($personalUser);
        $manager->persist($adminUser);
        $manager->persist($simpleAdminUser);
        $manager->flush();

        /*  $adminCanViewUserProfileUser = $this->getFakeUserObject($manager, 'admincanviewuserprofile@admin.hu', true, false, true, false, array('ROLE_ADMIN', 'ROLE_ADMIN_CAN_EDIT_USER_PROFILE'), 5);
          $adminCanEditUserProfileUser = $this->getFakeUserObject($manager, 'admincanedituserprofile@admin.hu', true, false, true, false, array('ROLE_ADMIN', 'ROLE_ADMIN_CAN_EDIT_USER_PROFILE', 'ROLE_ADMIN_CAN_EDIT_USER_PROFILE'), 6);
          $adminForRoleTesting1 = $this->getFakeUserObject($manager, 'adminforroles1@admin.hu', true, false, true, false, array('ROLE_ADMIN_CAN_DELETE_STORAGE', 'ROLE_ADMIN_CAN_EDIT_USER_PROFILE', 'ROLE_ADMIN_CAN_SEARCH'), 7);
          $adminForRoleTesting2 = $this->getFakeUserObject($manager, 'adminforroles2@admin.hu', true, false, true, false, array('ROLE_ADMIN_CAN_DELETE_STORAGE', 'ROLE_ADMIN_CAN_EDIT_USER_PROFILE', 'ROLE_ADMIN_CAN_SEARCH', 'ROLE_ADMIN_CAN_UPLOAD_DOCUMENT', 'ROLE_ADMIN_CAN_EDIT_DOCUMENT'), 8);
          $adminForRoleTesting3 = $this->getFakeUserObject($manager, 'adminforroles3@admin.hu', true, false, true, false, array('ROLE_ADMIN_CAN_SEARCH', 'ROLE_ALLOWED_TO_SWITCH', 'ROLE_ADMIN_CAN_EDIT_FAQ', 'ROLE_ADMIN_CAN_EDIT_ERROR_REPORTS'), 9);
          $adminForRoleTesting4 = $this->getFakeUserObject($manager, 'adminforroles4@admin.hu', true, false, true, false, array('ROLE_ADMIN_CAN_SEARCH', 'ROLE_ALLOWED_TO_SWITCH', 'ROLE_ADMIN_CAN_DELETE_DOCUMENT', 'ROLE_ADMIN_CAN_UPLOAD_ADMIN_DOCUMENT'), 10);
          $manager->persist($adminCanViewUserProfileUser);
          $manager->persist($adminCanEditUserProfileUser);
          $manager->persist($adminForRoleTesting1);
          $manager->persist($adminForRoleTesting2);
          $manager->persist($adminForRoleTesting3);
          $manager->persist($adminForRoleTesting4);
          $manager->flush(); */

        //Main creation of users. The -3 is due to the 3 user objects for the easier logins.
        for ($i = 0; $i < self::USER_COUNT - 3; $i++) {
            $user = $this->getFakeUserObject($manager, $this->getFakeUsername($i + 3), null, null, array(), $i + 3);
            $manager->persist($user);
            $manager->flush();
      }
      
//        } catch (\Exception $e) {
//          var_dump($e);
//      }
    }

    /**
     * Constructs a random User object depending on the index.
     * 
     * @param ObjectManager $manager
     * @param string|null $username
     * @param boolean $easyLogin
     * @param boolean $isSuperAdmin If $easyLogin is true then if this is true the user object will be superadmin. 
     * @param array $roles The user gets the contained roles.
     * @param int $index
     * @return User
     */
    private function getFakeUserObject($manager, $username, $easyLogin = false, $isSuperAdmin = false, $roles = array(), $index) {
        $user = new User();
        $email = $this->getFakeEmail($index);

        $user->setConfirmationToken($this->getFakeConfirmationToken($index));
        $user->setEmail($email);
        $user->setEmailCanonical(strtolower($email));
        $user->setCreatedAt($this->getFakeCreatedAt($index));
        $user->setPlainPassword('password');
        $user->setPassword('password');
        $user->setUsernameCanonical(strtolower($username));
        $user->setUsername($username);
        $user->setLanguage($this->getFakeLanguage($index));
        $user->setAddress($this->getAddress($index));
        $user->setBirthDate($this->getFakeBirthDate($index));
        mt_srand($index);
        if (mt_rand(0, 1)) {
            $user->setGender(User::GENDER_MALE);
        } else {
            $user->setGender(User::GENDER_FEMALE);
        }      
        $user->setUserIntroduction($this->getFakeUserIntroduction($index));
        $user->setUserIdeal($this->getFakeUserIdeal($index + 1, $user->getAddress()));
        if ($easyLogin) {
            $user->setSuperAdmin($isSuperAdmin);
            $user->setEnabled(true);
            $user->setLocked(false);
        } else {
            mt_srand($index + 1);
            if (mt_rand(0, 10) > 9) {
                $user->setSuperAdmin(true);
            } else {
                $user->setSuperAdmin(false);
            }
            mt_srand($index + 2);
            if (mt_rand(0, 10) > 1) {
                $user->setEnabled(true);
            } else {
                $user->setEnabled(false);
            }
            mt_srand($index + 3);
            mt_srand($index + 4);
            if (mt_rand(0, 5) > 3 && $user->isEnabled()) {
                $user->setLocked(true);
            } else {
                $user->setLocked(false);
            }
            mt_srand($index + 5);
        }

        foreach ($roles as $role) {
            $user->addRole($role);
        }

        return $user;
    }

    /**
     * Creates as many RoleSets as given by $count with random roles from the role hierarchy. 
     * 
     * @param EntityManager $manager
     * @param int $count
     */
    /* private function createRoleSets($manager, $count) {
      $everyRoles = $this->getEveryRoles();
      array_pop($everyRoles);
      for ($i = 1; $i <= $count; $i++) {
      $shuffledRolesArray = $this->seededArrayShuffle($everyRoles, $i);
      $rolesCount = mt_rand(1, count($everyRoles));
      $rolesOfNewRoleSet = array();
      for ($j = 0; $j < $rolesCount; $j++) {
      $rolesOfNewRoleSet[] = array_pop($shuffledRolesArray);
      }
      $rolesOfNewRoleSet = $this->container->get('core_user.role_manager')->filterRoles($rolesOfNewRoleSet, $this->container->getParameter('security.role_hierarchy.roles'));
      $roleSet = new RoleSet();
      $roleSet->setName('RoleSet' . $i);
      $roleSet->setRoles($rolesOfNewRoleSet);
      $manager->persist($roleSet);
      }
      $manager->flush();
      } */

    /**
     * Returns a flat array containing every roles from the role hierarchy.
     * 
     * @return array
     */
    /* private function getEveryRoles() {
      $everyRoles = array();
      foreach ($this->container->getParameter('security.role_hierarchy.roles') as $mainRole => $subRoles) {
      $everyRoles[] = $mainRole;
      foreach ($subRoles as $subRole) {
      $everyRoles[] = $subRole;
      }
      }
      return array_unique($everyRoles);
      } */

    /**
     * Shuffles an array in a repeatable manner, if the same $seed is provided.
     * 
     * @param array &$items The array to be shuffled.
     * @param int $seed The result of the shuffle will be the same for the same input ($items and $seed). If not given, uses the current time as seed.
     * @return void
     */
    /* private function seededArrayShuffle($array, $seed = false) {
      $arrayValues = array_values($array);
      mt_srand($seed ? $seed : time());
      for ($i = count($arrayValues) - 1; $i > 0; $i--) {
      $j = mt_rand(0, $i);
      list($arrayValues[$i], $arrayValues[$j]) = array($arrayValues[$j], $arrayValues[$i]);
      }
      return $arrayValues;
      } */

    /**
     * Creates and returns a new UserIntroduction instance with random values.
     * 
     * @param int $index
     * @return UserIntroduction
     */
    private function getFakeUserIntroduction($index) {
        mt_srand($index);
        
        $userIntroduction = new UserIntroduction();
        $this->setRandomValuesForUserIdealAndIntroductionCommon($userIntroduction, $index);
        $userIntroduction->setHeight(mt_rand(50, 300));
        $userIntroduction->setIntroduction(addslashes('A sörös tára fenyés talan sütő sugója. E sugó számára nagyon visszásak a robony véli, jeszegő rambók, csintó mesítékek, mivel visszás burája a dölés. Sárson az üdvös boragás a féstő, mely a menős két siskóban öbörögt. Ez tőzsdély, vidáklyásos és következetesen csaptatlag csípő vidaságoknak nyítos. A pori az egyik tumos kítán sugó, ezért robony iránta a golás. A gacsos morsakosokban sajnos gyakran tetőzték a dormányokat, hogy az idásokat nyugatra dalodják. A ládékony dormányok macskájának főzetében nyeglike togarc dormánynál szólátkodtak örököt éjjel-nappal a pitálék.'));
        $userIntroduction->setMotto(addslashes('Lórum ipse a művely, mint félet, és a szárus elgész. Az erej illene: 14 törös 65860 főzés ampózásban. Az erej illene: 14 törös 65860 főzés ampózásban. A pika kedik a respinen és a tadájával düllet. - jegyenség, nem fél, hogy szökleledik a szajuhány? '));
        $userIntroduction->setWeight(mt_rand(30, 400));
        
        return $userIntroduction;
    }
    
    /**
     * Creates and returns a new UserIdeal instance with random values.
     * 
     * @param int $index
     * @return UserIdeal
     */
    private function getFakeUserIdeal($index, $address) {
        mt_srand($index);
        
        $userIdeal = new UserIdeal();
        $userIdeal->setHeightFrom(mt_rand(50, 299));
        $userIdeal->setHeightTo(mt_rand($userIdeal->getHeightFrom() + 1, 300));
        $userIdeal->setWeightFrom(mt_rand(30, 399));
        $userIdeal->setWeightTo(mt_rand($userIdeal->getWeightFrom() + 1, 400));
        $userIdeal->setAgeFrom(mt_rand(18, 99));
        $userIdeal->setAgeTo(mt_rand($userIdeal->getAgeFrom() + 1, 100));      
        if (mt_rand(0, 1)) {
            $userIdeal->setGender(User::GENDER_MALE);
        } else {
            $userIdeal->setGender(User::GENDER_FEMALE);
        }  
        $userIdeal->setAddress($address);
        $this->setRandomValuesForUserIdealAndIntroductionCommon($userIdeal, $index);
        
        return $userIdeal;
    }
    
    /**
     * Sets random value to those fields of $userIdealOrIntroduction which are common between UserIdeal and UserIntroduction
     * 
     * @param UserIdeal|UserIntroduction $userIdealOrIntroduction
     * @param int $index
     */
    private function setRandomValuesForUserIdealAndIntroductionCommon($userIdealOrIntroduction, $index) {
        $userIdealOrIntroduction->setBodyShape($this->getBodyShape($index));
        $userIdealOrIntroduction->setEyeColor($this->getEyeColor($index));
        $userIdealOrIntroduction->setHairColor($this->getHairColor($index));
        $userIdealOrIntroduction->setHairLength($this->getHairLength($index));
        $userIdealOrIntroduction->setSearchingFor($this->getSearchingFor($index));
        $userIdealOrIntroduction->setWantTo($this->getWantTo($index));
    }
    
    /**
     * Returns one of the available body shape constants.
     * 
     * @param int $index
     * @return string
     */
    private function getBodyShape($index) {
        mt_srand($index);
        
        switch(mt_rand(0,5)) {
            case 0:
                return UserIntroduction::BODY_SHAPE_AVERAGE;
            case 1:
                return UserIntroduction::BODY_SHAPE_CHUBBY;
            case 2:
                return UserIntroduction::BODY_SHAPE_MUSCULAR;
            case 3:
                return UserIntroduction::BODY_SHAPE_OBESE;
            case 4:
                return UserIntroduction::BODY_SHAPE_SKINNY;   
            case 4:
                return UserIntroduction::BODY_SHAPE_SPORT;    
        }
    }
    
    /**
     * Returns one of the available eye color constants.
     * 
     * @param int $index
     * @return string
     */
    private function getEyeColor($index) {
        mt_srand($index);
        
        switch(mt_rand(0,3)) {
            case 0:
                return UserIntroduction::EYE_COLOR_BLUE;
            case 1:
                return UserIntroduction::EYE_COLOR_BROWN;
            case 2:
                return UserIntroduction::EYE_COLOR_GREEN;
            case 3:
                return UserIntroduction::EYE_COLOR_OTHER;
        }
    }
    
    /**
     * Returns one of the available hair color constants.
     * 
     * @param int $index
     * @return string
     */
    private function getHairColor($index) {
        mt_srand($index);
        
        switch(mt_rand(0,8)) {
            case 0:
                return UserIntroduction::HAIR_COLOR_BLACK;
            case 1:
                return UserIntroduction::HAIR_COLOR_BLLUE;
            case 2:
                return UserIntroduction::HAIR_COLOR_BLOND;
            case 3:
                return UserIntroduction::HAIR_COLOR_BROWN;
            case 4:
                return UserIntroduction::HAIR_COLOR_GRAY;  
            case 5:
                return UserIntroduction::HAIR_COLOR_GREEN;
            case 6:
                return UserIntroduction::HAIR_COLOR_OTHER;
            case 7:
                return UserIntroduction::HAIR_COLOR_PINK;
            case 8:
                return UserIntroduction::HAIR_COLOR_RED;    
        }
    }
    
    /**
     * Returns one of the available hair length constants.
     * 
     * @param int $index
     * @return string
     */
    private function getHairLength($index) {
        mt_srand($index);
        
        switch(mt_rand(0,3)) {
            case 0:
                return UserIntroduction::HAIR_LENGTH_BALD;
            case 1:
                return UserIntroduction::HAIR_LENGTH_LONG;
            case 2:
                return UserIntroduction::HAIR_LENGTH_MIDDLE;
            case 3:
                return UserIntroduction::HAIR_LENGTH_SHORT;
        }
    }
    
    /**
     * Returns one of the available searching for constants.
     * 
     * @param int $index
     * @return string
     */
    private function getSearchingFor($index) {
        mt_srand($index);
        
        switch(mt_rand(0,2)) {
            case 0:
                return UserIntroduction::SEARCHING_FOR_BOTH;
            case 1:
                return UserIntroduction::SEARCHING_FOR_MAN;
            case 2:
                return UserIntroduction::SEARCHING_FOR_WOMAN;
        }
    }
    
    /**
     * Returns one of the available want to constants.
     * 
     * @param int $index
     * @return string
     */
    private function getWantTo($index) {
        mt_srand($index);
        
        switch(mt_rand(0,2)) {
            case 0:
                return UserIntroduction::WANT_TO_FRIENDSHIP;
            case 1:
                return UserIntroduction::WANT_TO_RELATIONSHIP;
            case 2:
                return UserIntroduction::WANT_TO_SEX;
        }
    }
    
    /**
     * Get a random username. There is a random number after the username so each are unique.
     * 
     * @param int $index
     * @return string
     */
    private function getFakeUsername($index) {
        $usernames = array(
            array("username" => "Prince"),
            array("username" => "Wong"),
            array("username" => "Harris"),
            array("username" => "Quinn"),
            array("username" => "Langley"),
            array("username" => "Rodgers"),
            array("username" => "Alston"),
            array("username" => "Galloway"),
            array("username" => "Hull"),
            array("username" => "Roth"),
            array("username" => "Roaman"),
            array("username" => "Roman"),
            array("username" => "Mosley"),
            array("username" => "Sanchez"),
            array("username" => "Byers"),
            array("username" => "Ray"),
            array("username" => "Barrett"),
            array("username" => "Zamora"),
            array("username" => "Short"),
            array("username" => "Waller"),
            array("username" => "Conley"),
            array("username" => "Acosta"),
            array("username" => "Greene"),
            array("username" => "Britt"),
            array("username" => "Woodard"),
            array("username" => "Branch"),
            array("username" => "Ferrell"),
            array("username" => "Patton"),
            array("username" => "Gregory"),
            array("username" => "Case"),
            array("username" => "Lambert"),
            array("username" => "Page"),
            array("username" => "Emerson"),
            array("username" => "Gallagher"),
            array("username" => "Guy"),
            array("username" => "Wiley"),
            array("username" => "Hardy"),
            array("username" => "Coanley"),
            array("username" => "Cunningham"),
            array("username" => "Glass"),
            array("username" => "Lawson"),
            array("username" => "King"),
            array("username" => "Doyle"),
            array("username" => "Blevins"),
            array("username" => "Mcdaniel"),
            array("username" => "Nielsen"),
            array("username" => "Chang"),
            array("username" => "Rowe"),
            array("username" => "Mueller"),
            array("username" => "Craig"),
            array("username" => "Sosa"),
            array("username" => "Schneider"),
            array("username" => "Wright"),
            array("username" => "Villarreal"),
            array("username" => "Heath"),
            array("username" => "Barron"),
            array("username" => "Wallace"),
            array("username" => "Pate"),
            array("username" => "Juarez"),
            array("username" => "Pope"),
            array("username" => "Mccarty"),
            array("username" => "Morales"),
            array("username" => "Frank"),
            array("username" => "Miranda"),
            array("username" => "Holloway"),
            array("username" => "Salaainas"),
            array("username" => "England"),
            array("username" => "Rosa"),
            array("username" => "Cole"),
            array("username" => "Montgomery"),
            array("username" => "Mckay"),
            array("username" => "Dyer"),
            array("username" => "Gross"),
            array("username" => "Harvey"),
            array("username" => "Freeman"),
            array("username" => "Yates"),
            array("username" => "Mckenzie"),
            array("username" => "Leach"),
            array("username" => "Daugherty"),
            array("username" => "Wolfe"),
            array("username" => "Pace"),
            array("username" => "Richard"),
            array("username" => "Bradford"),
            array("username" => "Ratliff"),
            array("username" => "David"),
            array("username" => "Ferguson"),
            array("username" => "Hayden"),
            array("username" => "Mcintosh"),
            array("username" => "William"),
            array("username" => "Salinas"),
            array("username" => "Mullen"),
            array("username" => "Riddle"),
            array("username" => "Greer"),
            array("username" => "Vasquez"),
            array("username" => "Mcpherson"),
            array("username" => "Massey"),
            array("username" => "Odom"),
            array("username" => "Hughes"),
            array("username" => "Blackburn"),
            array("username" => "Watkins")
        );
        mt_srand($index);

        return $usernames[mt_rand(0, 99)]['username'] . mt_rand();
    }

    /**
     * Get a random email. There is a random number before the @ sign so emails are unique.
     * 
     * @param int $index
     * @return string
     */
    private function getFakeEmail($index) {
        $emails = array(
            array("email" => "in.magna@et.net"),
            array("email" => "rutrum.lorem.ac@asd.hu"),
            array("email" => "arcu.Sed.et@abc.co.uk"),
            array("email" => "quis@asd.eu"),
            array("email" => "neque.sed@sas.com"),
            array("email" => "Cras.sed.leo@dda.org"),
            array("email" => "cursus@et.net"),
            array("email" => "eget.massa.Suspendisse@asd.hu"),
            array("email" => "luctus.Curabitur.egestas@abc.co.uk"),
            array("email" => "vulputate.posuere.vulputate@asd.eu"),
            array("email" => "vitae@sas.com"),
            array("email" => "velit@dda.org"),
            array("email" => "adipiscing@et.net"),
            array("email" => "tempor.diam@asd.hu"),
            array("email" => "mattis.Cras.eget@abc.co.uk"),
            array("email" => "Duis@asd.eu"),
            array("email" => "mattis.velit.justo@sas.com"),
            array("email" => "laoreet@dda.org"),
            array("email" => "id.sapien.Cras@et.net"),
            array("email" => "sit@asd.hu"),
            array("email" => "neque@abc.co.uk"),
            array("email" => "interdum.ligula.eu@asd.eu"),
            array("email" => "nunc.risus@sas.com"),
            array("email" => "non.massa@dda.org"),
            array("email" => "Maecenas@et.net"),
            array("email" => "Praesent.eu@asd.hu"),
            array("email" => "Curabitur.egestas@abc.co.uk"),
            array("email" => "sodales.Mauris.blandit@asd.eu"),
            array("email" => "feugiat.Sed@sas.com"),
            array("email" => "arcu.Vestibulum.ut@dda.org"),
            array("email" => "dolor.dolor@et.net"),
            array("email" => "in@asd.hu"),
            array("email" => "dis.parturient@abc.co.uk"),
            array("email" => "In@asd.eu"),
            array("email" => "elit.pretium@sas.com"),
            array("email" => "est@dda.org"),
            array("email" => "mattis@et.net"),
            array("email" => "consectetuer.adipiscing@asd.hu"),
            array("email" => "Phasellus.in.felis@abc.co.uk"),
            array("email" => "pharetra.nibh.Aliquam@asd.eu"),
            array("email" => "tellus.id.nunc@sas.com"),
            array("email" => "tincidunt@dda.org"),
            array("email" => "non@et.net"),
            array("email" => "nec.cursus.a@asd.hu"),
            array("email" => "nisl.Quisque@abc.co.uk"),
            array("email" => "augue.scelerisque@asd.eu"),
            array("email" => "biga@sas.com"),
            array("email" => "amet.risus.Donec@dda.org"),
            array("email" => "ut.eros@et.net"),
            array("email" => "nascetur.ridiculus@asd.hu"),
            array("email" => "purus.ac.tellus@abc.co.uk"),
            array("email" => "rutrum.eu.ultrices@asd.eu"),
            array("email" => "vitaeo@sas.com"),
            array("email" => "dictum@dda.org"),
            array("email" => "massa.rutrum@et.net"),
            array("email" => "nulla.Integer@asd.hu"),
            array("email" => "lectus@abc.co.uk"),
            array("email" => "vitae.purus@asd.eu"),
            array("email" => "egestas.rhoncus.Proin@sas.com"),
            array("email" => "enim@dda.org"),
            array("email" => "vulputate.mauris.sagittis@et.net"),
            array("email" => "Proin@asd.hu"),
            array("email" => "sit.amet@abc.co.uk"),
            array("email" => "Ut@asd.eu"),
            array("email" => "cursus.a.enim@sas.com"),
            array("email" => "ac.mi.eleifend@dda.org"),
            array("email" => "sit.amet.nulla@et.net"),
            array("email" => "rutrum.justo.Praesent@asd.hu"),
            array("email" => "enim@abc.co.uk"),
            array("email" => "libero@asd.eu"),
            array("email" => "et.commodo.at@sas.com"),
            array("email" => "odio@dda.org"),
            array("email" => "Aliquam.tincidunt@et.net"),
            array("email" => "pulvinar@asd.hu"),
            array("email" => "non.lorem.vitae@abc.co.uk"),
            array("email" => "ultrices.a@asd.eu"),
            array("email" => "lorem@sas.com"),
            array("email" => "in.dolor.Fusce@dda.org"),
            array("email" => "justo@et.net"),
            array("email" => "Aliquam@asd.hu"),
            array("email" => "laoreet.libero@abc.co.uk"),
            array("email" => "urna@asd.eu"),
            array("email" => "nec@sas.com"),
            array("email" => "mi.Aliquam.gravida@dda.org"),
            array("email" => "euismod.mauris.eu@et.net"),
            array("email" => "neque.Morbi.quis@asd.hu"),
            array("email" => "Maecenas.libero@abc.co.uk"),
            array("email" => "Cum@asd.eu"),
            array("email" => "Praesent.interdum@sas.com"),
            array("email" => "Cras.interdum.Nunc@dda.org"),
            array("email" => "urna.et.arcu@et.net"),
            array("email" => "Vestibulum.ut@asd.hu"),
            array("email" => "sem.eget.massa@abc.co.uk"),
            array("email" => "Quisque@asd.eu"),
            array("email" => "rhoncus@sas.com"),
            array("email" => "vitae.risus.Duis@dda.org"),
            array("email" => "placerat.Cras@et.net"),
            array("email" => "Lorem@asd.hu"),
            array("email" => "elit.Curabitur.sed@abc.co.uk"),
            array("email" => "mauris.elit.dictum@asd.eu")
        );
        mt_srand($index);

        return implode(mt_rand() . '@', explode('@', $emails[mt_rand(0, 99)]['email']));
    }

    /**
     * Get a random DateTime which is beetween now and 5 years ago.
     * 
     * @param int
     * @return DateTime
     */
    private function getFakeDeletedAt($index) {
        mt_srand($index);
        $date = new \DateTime('2015-12-03');

        return $date->modify('-' . mt_rand(0, 60) . ' months');
    }

    /**
     * Get randomly a language code. 
     * 
     * @param int $index
     * @return string
     */
    private function getFakeLanguage($index) {
        mt_srand($index);

        return $this->supportedLanguagesArray[mt_rand(0, count($this->supportedLanguagesArray) - 1)];
    }

    /**
     * Get a random birth date. Every birthDate is more than 18 years ago from now.
     * 
     * @param int $index
     * @return DateTime
     */
    private function getFakeBirthDate($index) {
        mt_srand($index);
        $date = new \DateTime('2015-12-03');

        return $date->modify('-' . mt_rand(220, 1000) . ' months');
    }

    /**
     * Get randomly an Address entity.
     * 
     * @param int $index
     * @return Address
     */
    private function getAddress($index) {
        mt_srand($index);
        
        return $this->addressRepository->findBy(array(), null, 1, mt_rand(0, $this->addressCount - 1))[0];
    }

    /**
     * Get randomly a confirmation token.
     * 
     * @param int $index
     * @return string
     */
    private function getFakeConfirmationToken($index) {
        $confirmationTokens = array(
            array("confirmationToken" => "ZYG84ARQ6IS74yat0au8ktv3e9d3"),
            array("confirmationToken" => "TZP81FXV1EP20ixd0ag8ulo8k2d6"),
            array("confirmationToken" => "AOV46HZH6IP98ffh8dk1cjb0n9e3"),
            array("confirmationToken" => "VGM33XCQ4XC44hkp8ia8iks6l5o9"),
            array("confirmationToken" => "RLJ55DAT9YG50hgt8dw1ego4t7h8"),
            array("confirmationToken" => "YXC52OKQ7DQ05ejh9uh7gzg4n1p5"),
            array("confirmationToken" => "CFV81UBE4UE50deh0mw0zvj6t6f3"),
            array("confirmationToken" => "UUA12IPF8SI74mbx1qy0jah1x6j6"),
            array("confirmationToken" => "VOI82NRQ0HP28wif3or7yml5o3w6"),
            array("confirmationToken" => "YUJ12YPZ0SD43cbm3di1gib6k1l8"),
            array("confirmationToken" => "VJD74PJG7YE01vfg1lz8vgm6s6o5"),
            array("confirmationToken" => "VRK36DMR8QE70etp2ve4epy9v8v3"),
            array("confirmationToken" => "FPK69CJH8TR04vtt6cz0nib5f7b8"),
            array("confirmationToken" => "DTT54PKB6WS33qtr6yc6azn7m5o8"),
            array("confirmationToken" => "ZLC93OEB5UJ13phk3yy5apt9e3n2"),
            array("confirmationToken" => "TXV58UJR8MH73vmq5yo4izp4c4l8"),
            array("confirmationToken" => "FJH90TAA4HD02pqm9qg4shp1r4m4"),
            array("confirmationToken" => "PPS13TLO2VN20otn7ga8xbq2v6g3"),
            array("confirmationToken" => "SSE36HZV4TI19alr1pw7gjz0s4z7"),
            array("confirmationToken" => "ZFP25UYW1ZZ04ygc7wh9hrj2n8n9"),
            array("confirmationToken" => "JQT66EST6KF92kiz5sb3jqt0x1h6"),
            array("confirmationToken" => "ARF62JKR5AS44xlz8mx3vgs2m2y2"),
            array("confirmationToken" => "LHZ03UTJ3QE44rtf5xe7wob2q7t9"),
            array("confirmationToken" => "BDD46PUJ5OS81qcc5fa3afi6m8g0"),
            array("confirmationToken" => "VXM76ZGY8TF71msd1pn0ivg9y7v8"),
            array("confirmationToken" => "MJU43MAK1BD93xhz4pr7vsq0v4a1"),
            array("confirmationToken" => "KZE32JSG4AP69cus7zg1bdu5i1k9"),
            array("confirmationToken" => "GVL74LOR2MT70ufi2mq0gyj0i0r5"),
            array("confirmationToken" => "HKE09UJG5AA06pik5cu8iej4z4s3"),
            array("confirmationToken" => "XYE78JVE1DD56puv0it2ell8d9z3"),
            array("confirmationToken" => "HJD45RTT4XH84ndu1yg1ujm0g1j6"),
            array("confirmationToken" => "KXA92BOM5BJ96mxq8uu0dsg8m3c3"),
            array("confirmationToken" => "XSW04IXN7QN09ngn1uq9uoe3s9g2"),
            array("confirmationToken" => "DGY43FCF9ON88pht8gq4tfx9a9d1"),
            array("confirmationToken" => "QFP20WKI9DI27usr6mk1fiq0m3b9"),
            array("confirmationToken" => "KNH68NFD3EE73mcn5kp1izp6z8o4"),
            array("confirmationToken" => "MJC52FLB6IY79wdk3us7pqk6z9v5"),
            array("confirmationToken" => "JXY93QPP3FP00hzu9da7jte1i4g5"),
            array("confirmationToken" => "ZVG45STE3XH06pnj2ka9kjq1f2v5"),
            array("confirmationToken" => "BUN37ZOX2IF11ztz3cz9oxf8w0b7"),
            array("confirmationToken" => "VBB65HXH4FY48qsm8io7mmz7t9n4"),
            array("confirmationToken" => "YHA77LAA7KC33kvj8bu5siu5f5y6"),
            array("confirmationToken" => "QMX51DSL3VD39nol3pg0bxx0m9e7"),
            array("confirmationToken" => "HBD74PLE0VM91cnp7ad2ujg5g4e3"),
            array("confirmationToken" => "CQN07DKY2GK05hnf4fo3pjb1i9v0"),
            array("confirmationToken" => "GAE28AEL0MQ12lvr8nh9yzv9r9s4"),
            array("confirmationToken" => "JDM83QDB7IP48trb1qq8tec8a5c8"),
            array("confirmationToken" => "NHU13GJG8PS88zop0gr6fpx8j8u9"),
            array("confirmationToken" => "UBC16TSX6UV05bqi0ug9zue0v2n1"),
            array("confirmationToken" => "PUO32SDL7HK96heh6ru2leb3i2m1"),
            array("confirmationToken" => "TRK30CIG8DE96mbe8ra2cbs4b1f1"),
            array("confirmationToken" => "SUI21VTY2DI82ffc4tt0vqy4f6l5"),
            array("confirmationToken" => "LYI78FKK8VW58fsl9oq1woq3r7a1"),
            array("confirmationToken" => "GHY95LXP5QN12kyc8nx9ujm9k1n7"),
            array("confirmationToken" => "TRJ84AGZ3JF93fya1xc0xqg8t4b0"),
            array("confirmationToken" => "CRU95MUU6PE28zmo4vq7gcx4m0c7"),
            array("confirmationToken" => "XVV57EME7OU05wqk3wj6frf3y1c0"),
            array("confirmationToken" => "LGN78DAP3SM32ils7cv0osa4u0y3"),
            array("confirmationToken" => "JFR57HHE5JV98lar4ht3ngw0s8j3"),
            array("confirmationToken" => "DQJ74LNV3LI98pas5kp6rxy0y4u0"),
            array("confirmationToken" => "IXH93YAE0ZV54qnl8ys5iyb1s5h1"),
            array("confirmationToken" => "TQH43DWY3ZZ73bou3om3odv9v0e6"),
            array("confirmationToken" => "OBG59QIY5AB87vam4sn6iqk5n2i5"),
            array("confirmationToken" => "HHK93NSV9TA00xch7pq7abt4p4l4"),
            array("confirmationToken" => "NMV33QFC2JT27pnr9kv4taq0a1r8"),
            array("confirmationToken" => "SHX59PIS8FE45qbu7uo5vhc5q9e5"),
            array("confirmationToken" => "MQV68UVE6FH51zlu0zj4ryg4s0k0"),
            array("confirmationToken" => "IYC12LOU2DI71aeh8lh8qbw2f3v2"),
            array("confirmationToken" => "QGF57IYH9FI16mki9ws6fvz7j5y8"),
            array("confirmationToken" => "SCT29LNO4LS86rso1ur5eci3g4l4"),
            array("confirmationToken" => "XMQ47EPD8NF54hrt6tr7rsf1t6k3"),
            array("confirmationToken" => "BBE83WIB1XK81thb6dc6tcm1e8n5"),
            array("confirmationToken" => "XUM40LQV0ST43eyn9nj2dhx4z9f3"),
            array("confirmationToken" => "DEW50TRO8EE89kor3oa3nou6n2x4"),
            array("confirmationToken" => "HFJ12OOH5XM45qgu0uw8czf0f2f2"),
            array("confirmationToken" => "HOL19YZU7AP39hju1eu7dlc0v0h1"),
            array("confirmationToken" => "MOR07KQQ4UD90xyr5oz3gas2u6v5"),
            array("confirmationToken" => "SZC58LLV9VF86exj6to2sax9l2l4"),
            array("confirmationToken" => "WEJ19JKQ0GJ68jlr7ty0ipq1c1y5"),
            array("confirmationToken" => "TDC90GYV5KA74boi7vh7unm9n6b9"),
            array("confirmationToken" => "MCU38FIJ7ML19vnf8xf4ngx3b9k7"),
            array("confirmationToken" => "MQY33FLY1LX75xxu5wm7usz5u0n4"),
            array("confirmationToken" => "HZT66PPO9HZ59aff1rp9umh1y2e0"),
            array("confirmationToken" => "IIK67VFS3YA36zrf4fi6cwv8j1l3"),
            array("confirmationToken" => "HSR83QVU2PT91lfs1ol5gpp4m4p6"),
            array("confirmationToken" => "SPC98VRV0SB08zwm4zf4oni7c4u6"),
            array("confirmationToken" => "PJY93SCA2MQ01cds5av1jia7x6c5"),
            array("confirmationToken" => "TSD44SHK6PT37moo0bd2yrz7u8j2"),
            array("confirmationToken" => "HME14XYS2EU39pwp3hn4tnr7v8t1"),
            array("confirmationToken" => "IZP76LIZ0DI53abn6jo0zfs3k7z7"),
            array("confirmationToken" => "AAO70USE4GJ55ucy8dw2hgu1q9m8"),
            array("confirmationToken" => "ARE45EVL9XR97ozm2zv5byq0s1t0"),
            array("confirmationToken" => "XAJ26RQE7ZR95yuo8xr3awr2j3w7"),
            array("confirmationToken" => "GAQ78WEQ1EN80hvc3xs4tjq4l1k6"),
            array("confirmationToken" => "TUU92IOP5XO73wbn6pv4gmn7k3j7"),
            array("confirmationToken" => "OWS38LEQ7DW95xen3tf4ywd6r9c8"),
            array("confirmationToken" => "QNM93YAT3ZT25fry9cm4vox1g6w0"),
            array("confirmationToken" => "XYF50EZE7RF04urj8su0qoi5l7b3"),
            array("confirmationToken" => "UHD76GGN5YS37bjr8ir3blq9w3r3"),
            array("confirmationToken" => "XGG95QGW5YR17ghl2ec2iel1s8l5")
        );
        mt_srand($index);

        return $confirmationTokens[mt_rand(0, 99)]['confirmationToken'] . mt_rand();
    }

    /**
     * Get a created at date. Every date is between today and the date 5 years ago.
     * 
     * @param int
     * @return DateTime
     */
    private function getFakeCreatedAt($index) {
        mt_srand($index);
        $date = new \DateTime();

        return $date->modify('-' . mt_rand(0, 5*365) . ' days');
    }

    public function getOrder() {
        return 2;
    }

}
