<?php

namespace Core\CommonBundle\Exception;

use Core\CommonBundle\Exception\CoreException;
use Symfony\Component\HttpFoundation\Response;

class AccessDeniedException extends CoreException {
       
    /**
     * This exception should be thrown if a user doesn't have access for the requested resource.
     * 
     * @param string $message
     * @param string $domain
     * @param boolean $showNotify
     * @param boolean $useMessageForNotify
     * @param array $data
     * @param integer $code
     * @param \Exception $previous
     */
    public function __construct($message = 'Access denied!', $domain = CoreException::DEFAULT_TRANSLATION_DOMAIN, $showNotify = true, $useMessageForNotify = true, $data = array(), $code = 0, \Exception $previous = null) {
        parent::__construct(Response::HTTP_FORBIDDEN, $message, $domain, $showNotify, $useMessageForNotify, $data, $code, $previous);
    }

}

