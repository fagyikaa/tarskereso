<?php

namespace Core\UserBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class AuthenticationEntryPointListener implements AuthenticationEntryPointInterface {

    private $router;

    public function __construct(Router $router) {
        $this->router = $router;
    }

    /**
     * Returns a response that directs the user to authenticate, or if the request was an XmlHttpRequest
     * then returns a json indicating that the authorization failed.
     *
     * @param Request $request The request that resulted in an AuthenticationException
     * @param AuthenticationException $authException The exception that started the authentication process
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null) {
        if($request->isXmlHttpRequest()) {
            $array = array('auth' => false);
            $response = new JsonResponse($array, Response::HTTP_UNAUTHORIZED);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        return new RedirectResponse($this->router->generate('core_user_index'));
    }
}