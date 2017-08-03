<?php

namespace Core\CommonBundle\Exception;

use Core\CommonBundle\Exception\CoreException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;

class EntityValidationException extends CoreException {
    
    protected $errors;
    
    /**
     * This exception should be thrown if an entity validation fails.
     * 
     * @param string $message
     * @param string $domain
     * @param boolean $showNotify
     * @param boolean $useMessageForNotify
     * @param array $data
     * @param integer $code
     * @param \Exception $previous
     */
    public function __construct(ConstraintViolationList $errors, $message = 'Some error occured in the editing process!', $domain = CoreException::DEFAULT_TRANSLATION_DOMAIN, $showNotify = false, $useMessageForNotify = false, $data = array(), $code = 0, \Exception $previous = null) {
        $this->errors = $errors;
        parent::__construct(Response::HTTP_BAD_REQUEST, $message, $domain, $showNotify, $useMessageForNotify, $data, $code, $previous);
    }
    
    /**
     * Set errors
     * 
     * @param  ConstraintViolationList $errors
     * @return InvalidUserFieldEditException
     */
    public function setErrors(ConstraintViolationList $errors) {
        $this->errors = $errors;
        
        return $this;
    }
   
    /**
     * Return errors
     * 
     * @return ConstraintViolationList
     */
    public function getErrors() {
        return $this->errors;
    }
}