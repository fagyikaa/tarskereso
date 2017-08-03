<?php

namespace Core\CommonBundle\Exception;

class CoreException extends \Exception {
    
    const DEFAULT_TRANSLATION_DOMAIN = 'exception';
    
    protected $statusCode;
    protected $domain;
    protected $showNotify;
    protected $useMessageForNotify;
    protected $data;

    /**
     * This exception should be the parent of every custom exception which should be handled
     * by the exception handler.
     * 
     * @param string $message
     * @param string $domain
     * @param boolean $showNotify
     * @param boolean $useMessageForNotify
     * @param array $data
     * @param integer $code
     * @param \Exception $previous
     */
    public function __construct($statusCode, $message = 'Something went wrong!', $domain = self::DEFAULT_TRANSLATION_DOMAIN, $showNotify = true, $useMessageForNotify = true, $data = array(), $code = 0, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
        
        $this->statusCode = $statusCode;
        $this->domain = $domain;
        $this->showNotify = $showNotify;
        $this->useMessageForNotify = $useMessageForNotify;
        $this->data = $data;
    }
    
    /**
     * Get statusCode
     * 
     * @return int
     */
    public function getStatusCode() {
        return $this->statusCode;
    }
    
    /**
     * Set statusCode
     * 
     * @param int $statusCode
     */
    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
    }
    
    /**
     * Get domain
     * 
     * @return string
     */
    public function getDomain() {
        return $this->domain;
    }
    
    /**
     * Set domain
     * 
     * @param string $domain
     */
    public function setDomain($domain) {
        $this->domain = $domain;
    }
    
    /**
     * Get showNotify
     * 
     * @return string
     */
    public function getShowNotify() {
        return $this->showNotify;
    }
    
    /**
     * Set showNotify
     * 
     * @param boolean $showNotify
     */
    public function setShowNotify($showNotify) {
        $this->showNotify = $showNotify;
    }
    
    /**
     * Get useMessageForNotify
     * 
     * @return string
     */
    public function getUseMessageForNotify() {
        return $this->useMessageForNotify;
    }
    
    /**
     * Set useMessageForNotify
     * 
     * @param boolean $useMessageForNotify
     */
    public function setUseMessageForNotify($useMessageForNotify) {
        $this->useMessageForNotify = $useMessageForNotify;
    }
    
    /**
     * Get data
     * 
     * @return array
     */
    public function getData() {
        return $this->data;
    }
    
    /**
     * Set data
     * 
     * @param array $data
     */
    public function setData($data) {
        $this->data = $data;
    }

}
