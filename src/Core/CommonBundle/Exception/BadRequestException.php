<?php

namespace Core\CommonBundle\Exception;

use Core\CommonBundle\Exception\CoreException;
use Symfony\Component\HttpFoundation\Response;

class BadRequestException extends CoreException {
       
    /**
     * This exception should be thrown on bad requests.
     * 
     * @param string $message
     * @param string $domain
     * @param boolean $showNotify
     * @param boolean $useMessageForNotify
     * @param array $data
     * @param integer $code
     * @param \Exception $previous
     */
    public function __construct($message = 'Bad request!', $domain = CoreException::DEFAULT_TRANSLATION_DOMAIN, $showNotify = false, $useMessageForNotify = false, $data = array(), $code = 0, \Exception $previous = null) {
        parent::__construct(Response::HTTP_BAD_REQUEST, $message, $domain, $showNotify, $useMessageForNotify, $data, $code, $previous);
    }

}

