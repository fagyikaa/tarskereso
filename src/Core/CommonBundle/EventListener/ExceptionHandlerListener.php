<?php

namespace Core\CommonBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Core\CommonBundle\Exception\InvalidFormException;
use Core\CommonBundle\Exception\EntityValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\TranslatorInterface;
use Core\CommonBundle\Exception\CoreException;
use JMS\Serializer\Serializer;

class ExceptionHandlerListener {

    protected $translator;
    protected $serializer;

    public function __construct(TranslatorInterface $translator, Serializer $serializer) {
        $this->translator = $translator;
        $this->serializer = $serializer;
    }

    /**
     * Sets the $event's response to a custom jsonResponse according to the type of exception.
     * For our app exceptions it creates a json with message, data, showNotify and useMessageForNotify
     * properties. Message is the translated message of the exception's message which should be a 
     * translator key. The data contains any data, including the form errors or array of error 
     * messages in case of validation error. If showNotify is true the frontend should popup
     * Notify message, if useMessageForNotify then instead of general message the Notify
     * should use the message property of the returned response. For every other exception
     * simply the message of the exception is returned under message key.
     * 
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event) {
        $exception = $event->getException();

        $statusCode = $this->getStatusCode($exception);

        $response = $this->getResponse($exception);

        if (is_null($statusCode)) {
            $jsonResponse = new JsonResponse($response);
        } else {
            $jsonResponse = new JsonResponse($response, $statusCode);
        }

        $event->setResponse($jsonResponse);
    }

    /**
     * Returns response's data (message, data, notify showing, using message for notify) based on the exception
     * 
     * @param \Exception $exception
     * @return array
     */
    private function getResponse(\Exception $exception) {
        $response = array(
            'message' => '',
            'data' => array(),
            'showNotify' => true,
            'useMessageForNotify' => true
        );

        if ($exception instanceof InvalidFormException) { // Form errors
            $response['data'] = $this->serializer->serialize($exception->getFormErrors(), 'json');
            $response['showNotify'] = $exception->getShowNotify();
            $response['useMessageForNotify'] = $exception->getUseMessageForNotify();
        } else if ($exception instanceof EntityValidationException) { // Validation errors
            $errors = array();
            foreach ($exception->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
            
            $response['data'] = $this->serializer->serialize($errors, 'json');
            $response['showNotify'] = $exception->getShowNotify();
            $response['useMessageForNotify'] = $exception->getUseMessageForNotify();
        } else if ($exception instanceof CoreException) { // Custom exceptions
            $response['message'] = $this->translator->trans($exception->getMessage(), array(), $exception->getDomain());
            $response['data'] = $exception->getData();
            $response['showNotify'] = $exception->getShowNotify();
            $response['useMessageForNotify'] = $exception->getUseMessageForNotify();
        } else {
            $response['message'] = $exception->getMessage();
        }

        return $response;
    }

    /**
     * Returns status code of the exception if it is set or else null
     * 
     * @param \Exception $exception
     * @return int|null
     */
    private function getStatusCode(\Exception $exception) {
        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }

        return null;
    }

}
