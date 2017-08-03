<?php

namespace Core\CommonBundle\Twig;

use Hashids\Hashids;

class HashIdExtension extends \Twig_Extension {

    private $hashids;

    public function __construct(Hashids $hashids) {
        $this->hashids = $hashids;
    }

    /**
     * The filter's name: hashids_encode
     * 
     * @return array
     */
    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('hashids_encode', array($this, 'hashidsEncodeFilter')),
        );
    }

    /**
     * Returns the hash generated from the given integer $value.
     * 
     * @param int $value
     * @return string
     */
    public function hashidsEncodeFilter($value) {
        return $this->hashids->encode($value);
    }

    public function getName() {
        return 'hashid_extension';
    }

}
