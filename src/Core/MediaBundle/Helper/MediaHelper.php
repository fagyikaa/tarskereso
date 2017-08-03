<?php

namespace Core\MediaBundle\Helper;

use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

class MediaHelper {

    protected $liipFilterConfiguration;

    public function __construct(FilterConfiguration $liipFilterConfiguration) {
        $this->liipFilterConfiguration = $liipFilterConfiguration;
    }
    
    /**
     * Returns the list of mime types that are allowed to upload.
     * 
     * @return array
     */
    public function getAllowedMimetypes() {
        return array(
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        );
    }

    /**
     * If the $request contains size parameter then overrides the thumbnail size for liip imagine bundle
     * defined in the config.yml.
     * 
     * @param int|string $size
     * @param string $filter
     */
    public function setPreviewImageSizeForServing($size, $filter) {
        if (is_numeric($size)) {
            # The filter configuration service
            $filterConfiguration = $this->liipFilterConfiguration;

            # Get the filter settings
            $config = $filterConfiguration->get($filter);

            # Update filter settings
            $config['filters']['thumbnail']['size'] = array($size, $size);
            $filterConfiguration->set($filter, $config);
        }
    }

}
