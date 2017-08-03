<?php

namespace Core\CommonBundle\Exception;

use Core\CommonBundle\Exception\CoreException;
use Symfony\Component\HttpFoundation\Response;

class EntityAlreadyDeletedException extends CoreException {
       
    /**
     * This exception should be thrown if the user wants to delete an already deleted entity.
     * 
     * @param string $message
     * @param string $domain
     * @param boolean $showNotify
     * @param boolean $useMessageForNotify
     * @param array $data
     * @param integer $code
     * @param \Exception $previous
     */
    public function __construct($message = 'Entity already deleted!', $domain = CoreException::DEFAULT_TRANSLATION_DOMAIN, $showNotify = true, $useMessageForNotify = true, $data = array(), $code = 0, \Exception $previous = null) {
        parent::__construct(Response::HTTP_BAD_REQUEST, $message, $domain, $showNotify, $useMessageForNotify, $data, $code, $previous);
    }

}

