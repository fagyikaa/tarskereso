<?php

namespace Core\MediaBundle\Helper;

use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Core\MediaBundle\Controller\ApiImageController;
use Core\MediaBundle\Exception\ImagePreviewCacheResolverException;

class ImagePreviewCacheResolver implements ResolverInterface {

    private $filesystem;
    private $router;
    private $filesRoot;
    private $cachePrefix;
    private $imageId;
    private $filterConfiguration;

    /**
     * @param Filesystem     $filesystem
     * @param Router         $router
     * @param FilterConfiguration        $filterConfiguration
     */
    public function __construct(Filesystem $filesystem, Router $router, FilterConfiguration $filterConfiguration) {
        $this->filesystem = $filesystem;
        $this->router = $router;
        $this->filterConfiguration = $filterConfiguration;
        $this->filesRoot = __DIR__ . '/../../../../media/';
        $this->cachePrefix = 'preview_cache';
    }

    /**
     * Returns route for api_core_media_serve_image with type IMAGE_SERVING_TYPE_THUMBNAIL which
     * indicates to serve thumbnail instead of the actual image.
     * 
     * @param string $path
     * @param string $filter
     * @return string 
     */
    public function resolve($path, $filter) {
        if (is_null($this->imageId) || $this->imageId === 0) {
            throw new ImagePreviewCacheResolverException('media.image_preview_cache_resolver.resolve');
        }
        return $this->router->generate('api_core_media_serve_image', array(
                    'imageId' => $this->imageId,
                    'type' => ApiImageController::IMAGE_SERVING_TYPE_THUMBNAIL,
                    'size' => $this->getImageSize($filter)));
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
        $this->filesystem->dumpFile(
                $this->getFilePath($path, $filter), $binary->getContent()
        );
    }

    /**
     * Removes the given thumbnail (for every size).
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
                $fileName = array_pop($explodedPath);
                $sanitizedPath = '';
                foreach ($explodedPath as $pathPiece) {
                    $sanitizedPath .= $pathPiece . '/';
                }
                $files = glob($sanitizedPath . '*');
                foreach ($files as $file) {
                    if (is_file($file) && $this->isIdentical($file, $fileName)) {
                        unlink($file);
                    }
                }
            }
        }
    }

    /**
     * Sets the imageId which then used to generate correct route in resolve.
     * 
     * @param int $imageId
     */
    public function setImageId($imageId) {
        $this->imageId = $imageId;
    }

    /**
     * Returns a full url from the path (which passed in the controller to liip's controller->filterAction()).
     * The url will looks like ...media/users/~userhash~/images/preview_cache/~imagename~ 
     * 
     * @param string $path
     * @param string $filter
     * @return string
     */
    private function getFilePath($path, $filter) {
        return $this->filesRoot . '/' . $this->getFileUrl($path, $filter);
    }

    /**
     * Returns a full url from the path (which passed in the controller to liip's controller->filterAction()).
     * The url will looks like users/~userhash~/images/preview_cache/~imagename~_~size~.~imageExtensions~ 
     * 
     * @param string $path
     * @param string $filter
     * @return string
     */
    private function getFileUrl($path, $filter) {
        // crude way of sanitizing URL scheme ("protocol") part
        $path = str_replace('://', '---', $path);
        $explodedPath = explode('/', $path);
        $imageFullName = array_pop($explodedPath);
        array_pop($explodedPath);

        $imageNamme = substr($imageFullName, 0, -4);
        $imageExtension = substr($imageFullName, -4);

        $path = ''; //will be '/users/*userhash*/'
        foreach ($explodedPath as $pathPiece) {
            $path .= $pathPiece . '/';
        }
        return $path . '/' . $this->cachePrefix . '/' . $imageNamme . '_' . $this->getImageSize($filter) . $imageExtension;
    }

    /**
     * Returns the required thumbnail size, default size is set in config, however the size changes if its passed in the url.
     * 
     * @param string $filter
     * @return string
     */
    private function getImageSize($filter) {
        $config = $this->filterConfiguration->get($filter);
        return $config['filters']['thumbnail']['size'][0];
    }

    /**
     * Checks that the given $file's name is identical to the given $fileName.
     * The $file's name is identical if only the size extension different.
     * 
     * @param File $file
     * @param string $fileName
     * @return boolean
     */
    private function isIdentical($file, $fileName) {
        $explodedBaseFileName = explode('_', basename($file));
        $explodedFileName = explode('_', $fileName);

        return $explodedBaseFileName[0] === $explodedFileName[0];
    }

}
