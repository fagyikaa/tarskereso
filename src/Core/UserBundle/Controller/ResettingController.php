<?php

namespace Core\UserBundle\Controller;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Model\UserInterface;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ResettingController extends FOSRestController {

    /**
     * Request reset user password: submit form and send email
     * 
     * @param Request $request
     * @return json
     */
    public function sendEmailAction(Request $request) {
        $username = $request->request->get('email');
        $user = $this->get('fos_user.user_manager')->findUserByEmail($username);

        if (is_null($user)) {
            $view = $this->view($this->get('translator')->trans('form.resetting.not_found', array(), 'index'), Response::HTTP_NOT_FOUND);
        } elseif ($user->isPasswordRequestNonExpired($this->getParameter('fos_user.resetting.token_ttl'))) {
            $view = $this->view($this->get('translator')->trans('form.resetting.non_expired', array(), 'index'), Response::HTTP_BAD_REQUEST);
        } elseif (is_null($user->getConfirmationToken())) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        if (false === isset($view)) {
            $this->get('fos_user.mailer')->sendResettingEmailMessage($user);
            $user->setPasswordRequestedAt(new \DateTime());
            $this->get('fos_user.user_manager')->updateUser($user);
            $view = $this->view(null, Response::HTTP_NO_CONTENT);
        }

        return $this->handleView($view);
    }

    /**
     * Tell the user to check his email provider
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function checkEmailAction(Request $request) {
        $email = $request->query->get('email');

        if (empty($email)) {
            // the user does not come from the sendEmail action
            return new RedirectResponse($this->generateUrl('fos_user_resetting_request'));
        }

        return $this->render('FOSUserBundle:Resetting:checkEmail.html.twig', array(
                    'email' => $email,
        ));
    }

    /**
     * Reset user password
     */
    public function resetAction(Request $request, $token) {
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.resetting.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            $view = $this->view()
                            ->setTemplate('CoreUserBundle:Resetting:reset.html.twig')
                            ->setTemplateData(array(
                                'token' => $token,
                            ))->setFormat('html');

            return $this->handleView($view);
        }

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_INITIALIZE, $event);

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_SUCCESS, $event);

            $userManager->updateUser($user);

            $this->addFlash('success', $this->get('translator')->trans('user.resetting.success_flash_bag', array(), 'messages'));
            $view = $this->redirectView($this->generateUrl('core_common_homepage'), Response::HTTP_MOVED_PERMANENTLY);           
            return $this->handleView($view);
        }

        $view = $this->view()
                        ->setTemplate('CoreUserBundle:Resetting:reset.html.twig')
                        ->setTemplateData(array(
                            'token' => $token,
                            'form' => $form->createView(),
                        ))->setFormat('html');

        return $this->handleView($view);
    }

    /**
     * Get the truncated email displayed when requesting the resetting.
     *
     * The default implementation only keeps the part following @ in the address.
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     *
     * @return string
     */
    protected function getObfuscatedEmail(UserInterface $user) {
        $email = $user->getEmail();
        if (false !== $pos = strpos($email, '@')) {
            $email = '...' . substr($email, $pos);
        }

        return $email;
    }

}
