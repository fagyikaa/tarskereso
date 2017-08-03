<?php

namespace Core\CommonBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Core\CommonBundle\Helper\CommonHelper;

class LocaleRedirectListener {

    protected $router;
    protected $supportedLanguages;

    public function __construct(Router $router, CommonHelper $commonHelper) {
        $this->router = $router;
        $this->supportedLanguages = $commonHelper->getSupportedLanguagesArray();
    }

    /**
     * If the requested route was not found but only because it doesn't contain the locale prefix
     * then adds the locale prefix to the url and returns a redirect response. 
     * 
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event) {
        $exception = $event->getException();

        if ($exception instanceof NotFoundHttpException) {
            $event->getRequest()->setLocale($event->getRequest()->getPreferredLanguage($this->supportedLanguages));
            try {
                $params = $this->router->match('/' . $event->getRequest()->getLocale() . $event->getRequest()->getPathInfo());
                $route = $params['_route'];
                unset($params['_route']);
                $url = $this->router->generate($route, $params);
                $response = new RedirectResponse($url);
                $event->setResponse($response);
            } catch (ResourceNotFoundException $exception) {
                //If the path would be correct but the locale given is bad (not supported or not a valid locale) then redirects to a valid one
                $explodedPath = explode('/', $event->getRequest()->getPathInfo());
                //The first element is empty string, the second should contain the locale
                $explodedPath[1] = $event->getRequest()->getPreferredLanguage($this->supportedLanguages);
                //If only a bad or invalid locale is given
                if (count($explodedPath) === 2) {
                    $explodedPath[] = '';
                }
                $params = $this->router->match(implode('/', $explodedPath));
                $route = $params['_route'];
                unset($params['_route']);
                $url = $this->router->generate($route, $params);
                $response = new RedirectResponse($url);
                $event->setResponse($response);
            }
        }
    }

}



