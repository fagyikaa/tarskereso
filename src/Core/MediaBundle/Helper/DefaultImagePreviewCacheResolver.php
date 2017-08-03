<?php

namespace Core\MediaBundle\Helper;

use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RequestContext;

class DefaultImagePreviewCacheResolver implements ResolverInterface {

    private $filesystem;
    private $previewsRoot;
    private $filterConfiguration;
    private $cachePrefix;
    private $requestContext;

    /**
     * @param Filesystem     $filesystem
     * @param FilterConfiguration     $filterConfiguration
     * @param RequestContext     $requestContext
     */
    public function __construct(Filesystem $filesystem, FilterConfiguration $filterConfiguration, RequestContext $requestContext) {
        $this->filesystem = $filesystem;
        $this->filterConfiguration = $filterConfiguration;
        $this->requestContext = $requestContext;
        $this->previewsRoot = __DIR__ . '/../../../../web/images/image_previews';
        $this->cachePrefix = 'images/image_previews';
    }

    /**
     * Returns route for the cached default profile image based on filter.
     * 
     * @param string $path
     * @param string $filter
     * @return string 
     */
    public function resolve($path, $filter) {
        return sprintf('%s/%s', $this->getBaseUrl(), $this->cachePrefix . '/' . $filter . '/' . $this->getFileUrl($path, $filter));
    }

    /**
     * Checks if the preview is already cached or needs to be made.
     * 
     * @param string $path
     * @param string $filter
     * @return boolean
     */
    public function isStored($path, $filter) {
        return is_file($this->getFilePath($path, $filter));
    }

    /**
     * Store's the given binary (image) to the given path
     * 
     * @param BinaryInterface $binary
     * @param string $path
     * @param string $filter
     */
    public function store(BinaryInterface $binary, $path, $filter) {
        $this->filesystem->dumpFile($this->getFilePath($path, $filter), $binary->getContent());
    }

    /**
     * Removes every images in the user's preview directory.
     * 
     * @param array $paths
     * @param array $filters
     * @return 
     */
    public function remove(array $paths, array $filters) {
        if (empty($paths) && empty($filters)) {
            return;
        }
        if (empty($paths)) {
            return;
        }
        foreach ($paths as $path) {
            foreach ($filters as $filter) {
                $explodedPath = explode('/', $this->getFilePath($path, $filter));
                array_pop($explodedPath);
                $sanitizedPath = '';
                foreach ($explodedPath as $pathPiece) {
                    $sanitizedPath .= $pathPiece . '/';
                }
                $files = glob($sanitizedPath . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
    }

    /**
     * Returns a full url from the path (which passed in the controller to liip's controller->filterAction()).
     * The url will looks like ../web/images/image_previews/default_profile_image_preview/default_~size~.jpg.
     * ~size~ is the size of the thumbnail.
     * 
     * @param string $path
     * @param tstring $filter
     * @return string
     */
    private function getFilePath($path, $filter) {
        return $this->previewsRoot . '/' . $filter . '/' . $this->getFileUrl($path, $filter);
    }

    /**
     * Returns file url based on the required size of the thumbnail. The url will looks like
     * default_~size~.jpg. ~size~ is the size of the thumbnail.
     * 
     * @param string $path
     * @param string $filter
     * @return string
     */
    private function getFileUrl($path, $filter) {
        // crude way of sanitizing URL scheme ("protocol") part
        $path = str_replace('://', '---', $path);
        $explodedPath = explode('.', $path);
        return $explodedPath[0] . '_' . $this->getImageSize($filter) . '.' . $explodedPath[1];
    }

    /**
     * Returns the required thumbnail size, default size is set in config, however the size is changing if its passed in the url.
     * 
     * @param string $filter
     * @return string
     */
    private function getImageSize($filter) {
        $config = $this->filterConfiguration->get($filter);
        return $config['filters']['thumbnail']['size'][0];
    }

    /**
     * Returns the base url for the page with correct protocol.
     * 
     * @return string
     */
    private function getBaseUrl() {
        $port = '';
        if ('https' == $this->requestContext->getScheme() && $this->requestContext->getHttpsPort() != 443) {
            $port = ":{$this->requestContext->getHttpsPort()}";
        }
        if ('http' == $this->requestContext->getScheme() && $this->requestContext->getHttpPort() != 80) {
            $port = ":{$this->requestContext->getHttpPort()}";
        }
        $baseUrl = $this->requestContext->getBaseUrl();
        if ('.php' == substr($this->requestContext->getBaseUrl(), -4)) {
            $baseUrl = pathinfo($this->requestContext->getBaseurl(), PATHINFO_DIRNAME);
        }
        $baseUrl = rtrim($baseUrl, '/\\');
        return sprintf('%s://%s%s%s', $this->requestContext->getScheme(), $this->requestContext->getHost(), $port, $baseUrl
        );
    }

}
