<?php

namespace Core\CommonBundle\Exception;

use Symfony\Component\Form\FormErrorIterator;
use Core\CommonBundle\Exception\CoreException;
use Symfony\Component\HttpFoundation\Response;

class InvalidFormException extends CoreException  {
    
    protected $formErrors;
    
    /**
     * This exception should be thrown if a form is invalid.
     * 
     * @param FormErrorIterator $formErrors
     * @param string $message
     * @param string $domain
     * @param boolean $showNotify
     * @param boolean $useMessageForNotify
     * @param array $data
     * @param integer $code
     * @param \Exception $previous
     */
    public function __construct(FormErrorIterator $formErrors, $message = 'Form validation error!', $domain = CoreException::DEFAULT_TRANSLATION_DOMAIN, $showNotify = false, $useMessageForNotify = false, $data = array(), $code = 0, \Exception $previous = null) {
        $this->formErrors = $formErrors;
        parent::__construct(Response::HTTP_BAD_REQUEST, $message, $domain, $showNotify, $useMessageForNotify, $data, $code, $previous);
    }
    
    /**
     * Set formErrors
     * 
     * @param  FormErrorIterator $formErrors
     * @return InvalidFormException
     */
    public function setFormErrors(FormErrorIterator $formErrors) {
        $this->formErrors = $formErrors;
        
        return $this;
    }
   
    /**
     * Return formErrors
     * 
     * @return FormErrorIterator
     */
    public function getFormErrors() {
        return $this->formErrors;
    }
}