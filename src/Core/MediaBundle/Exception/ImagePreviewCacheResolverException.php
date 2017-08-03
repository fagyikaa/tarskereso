<?php

namespace Core\MediaBundle\Exception;

use Core\CommonBundle\Exception\CoreException;
use Symfony\Component\HttpFoundation\Response;

class ImagePreviewCacheResolverException extends CoreException {
    
    /**
     * This exception should be thrown if a user wants to edit a property of an entity
     * which doesn't exist or not editable.
     * 
     * @param string $message
     * @param string $domain
     * @param boolean $showNotify
     * @param boolean $useMessageForNotify
     * @param array $data
     * @param integer $code
     * @param \Exception $previous
     */
    public function __construct($message = 'Not found property of entity!', $domain = CoreException::DEFAULT_TRANSLATION_DOMAIN, $showNotify = true, $useMessageForNotify = true, $data = array(), $code = 0, \Exception $previous = null) {
        parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $message, $domain, $showNotify, $useMessageForNotify, $data, $code, $previous);
    }

}
