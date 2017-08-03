<?php

namespace Core\MediaBundle\Helper;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

class UserIdHashDirectoryNamerForImageUpload implements DirectoryNamerInterface {

    private $hashIds;


    public function __construct($hashIds) {
        $this->hashIds = $hashIds;
    }

    /**
     * Returns a directory name like users/~userhash~/images/
     * 
     * @param Image $object
     * @param PropertyMapping $mapping
     * @return string
     */
    public function directoryName($object, PropertyMapping $mapping) {
        $userHash = $this->hashIds->encode($object->getOwner()->getId());  
        return 'users/' . $userHash . '/images/';
    }
}
