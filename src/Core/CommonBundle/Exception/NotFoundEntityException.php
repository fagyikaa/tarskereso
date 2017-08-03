<?php

namespace Core\CommonBundle\Exception;

use Core\CommonBundle\Exception\CoreException;
use Symfony\Component\HttpFoundation\Response;

class NotFoundEntityException extends CoreException {
    
    /**
     * This exception should be thrown if a user wants to get an entity which doesn't exists
     * or deleted and the user doesn't have the right to view it.
     * 
     * @param string $message
     * @param string $domain
     * @param boolean $showNotify
     * @param boolean $useMessageForNotify
     * @param array $data
     * @param integer $code
     * @param \Exception $previous
     */
    public function __construct($message = 'Not found entity!', $domain = CoreException::DEFAULT_TRANSLATION_DOMAIN, $showNotify = true, $useMessageForNotify = true, $data = array(), $code = 0, \Exception $previous = null) {
        parent::__construct(Response::HTTP_NOT_FOUND, $message, $domain, $showNotify, $useMessageForNotify, $data, $code, $previous);
    }

}
