<?php

namespace Core\UserBundle\Exception;

use Core\CommonBundle\Exception\CoreException;
use Symfony\Component\HttpFoundation\Response;

class InvalidCategoryException extends \Exception {    

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
    public function __construct($message = 'Invalid category!', $domain = CoreException::DEFAULT_TRANSLATION_DOMAIN, $showNotify = true, $useMessageForNotify = true, $data = array(), $code = 0, \Exception $previous = null) {
        parent::__construct(Response::HTTP_BAD_REQUEST, $message, $domain, $showNotify, $useMessageForNotify, $data, $code, $previous);
    }
}
