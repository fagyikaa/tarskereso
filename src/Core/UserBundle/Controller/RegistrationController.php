<?php

namespace Core\UserBundle\Controller;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Core\UserBundle\Entity\User;

class RegistrationController extends FOSRestController {

    /**
     * Renders the template for the registration form.
     * 
     * @return html
     */
    public function renderRegistrationFormAction() {
        $formFactory = $this->get('fos_user.registration.form.factory');
        $form = $formFactory->createForm();

        $view = $this->view()
                ->setTemplate('CoreUserBundle:Registration:registrationForm.html.twig')
                ->setTemplateData(array(
            'form' => $form->createView(),
        ));

        return $this->handleView($view);
    }

    /**
     * Registers a user or send back form with errors
     * 
     * @param Request $request
     * @return html
     */
    public function registerAction(Request $request) {
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);
        $user->setLanguage($request->getLocale());

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

            $userManager->updateUser($user);
            $view = $this->view(null, Response::HTTP_NO_CONTENT)->setFormat('json');
        } else {
            $view = $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST)->setFormat('json');
        }

        return $this->handleView($view);
    }

    /**
     * Tell the user to check his email provider
     * 
     * @return html
     */
    public function checkEmailAction() {
        $email = $this->get('session')->get('fos_user_send_confirmation_email/email');
        $this->get('session')->remove('fos_user_send_confirmation_email/email');
        $user = $this->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->render('FOSUserBundle:Registration:checkEmail.html.twig', array(
                    'user' => $user,
        ));
    }

    /**
     * If the confirmation token is valid then enable the user, login the user
     * and redirects to homepage.
     * 
     * @param Request $request
     * @param string $token
     * @return html
     */
    public function confirmAction(Request $request, $token) {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            $view = $this->view()
                    ->setTemplate('CoreUserBundle:Registration:confirmationInvalidToken.html.twig');
        } else {
            /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
            $dispatcher = $this->get('event_dispatcher');

            $user->setConfirmationToken(null);
            $user->setEnabled(true);

            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

            $userManager->updateUser($user);
            $this->login($user);

            $url = $this->generateUrl('core_common_homepage');
            $this->addFlash('success', $this->get('translator')->trans('confirmation.flash_bag', array(), 'registration'));
            $response = new RedirectResponse($url);
            $view = $this->redirectView($url);

            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));
        }
        
        return $this->handleView($view);
    }

    /**
     * Logins $user: creates UsernamePasswordToken and set it into session
     * 
     * @param User $user
     */
    private function login(User $user) {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));
    }
    
    /**
     * Tell the user his account is now confirmed
     * 
     * @return html
     */
    public function confirmedAction() {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render('FOSUserBundle:Registration:confirmed.html.twig', array(
                    'user' => $user,
                    'targetUrl' => $this->getTargetUrlFromSession(),
        ));
    }

    /**
     * Gets the target url from session
     * 
     * @return string|nothing
     */
    private function getTargetUrlFromSession() {
        // Set the SecurityContext for Symfony <2.6
        if (interface_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')) {
            $tokenStorage = $this->get('security.token_storage');
        } else {
            $tokenStorage = $this->get('security.context');
        }

        $key = sprintf('_security.%s.target_path', $tokenStorage->getToken()->getProviderKey());

        if ($this->get('session')->has($key)) {
            return $this->get('session')->get($key);
        }
    }

}
