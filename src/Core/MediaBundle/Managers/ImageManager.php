<?php

namespace Core\MediaBundle\Managers;

use Doctrine\ORM\EntityManagerInterface;
use Core\MediaBundle\Form\Type\UploadImageType;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Core\CommonBundle\Exception\EntityValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Core\MediaBundle\Helper\MediaHelper;
use Symfony\Component\HttpFoundation\Request;
use Liip\ImagineBundle\Controller\ImagineController;
use Core\MediaBundle\Entity\Image;
use Hashids\HashGenerator;
use Core\MediaBundle\Helper\ImagePreviewCacheResolver;
use Core\MediaBundle\Controller\ApiImageController;
use Core\CommonBundle\Exception\NotFoundPropertyException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Core\CommonBundle\Exception\NotFoundEntityException;
use Core\CommonBundle\Exception\InvalidFormException;

class ImageManager {

    const IMAGE_PREVIEW = 'image_preview';

    protected $uploadImageType;
    protected $em;
    protected $translator;
    protected $formFactory;
    protected $mediaHelper;
    protected $liip;
    protected $hashIds;
    protected $cacheResolver;
    protected $validator;
    protected $cacheManager;

    public function __construct(UploadImageType $uploadImageType, EntityManagerInterface $em, TranslatorInterface $translator, FormFactoryInterface $formFactory, MediaHelper $mediaHelper, ImagineController $liip, ImagePreviewCacheResolver $cacheResolver, HashGenerator $hashIds, ValidatorInterface $validator, CacheManager $cacheManager) {
        $this->uploadImageType = $uploadImageType;
        $this->em = $em;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->mediaHelper = $mediaHelper;
        $this->liip = $liip;
        $this->hashIds = $hashIds;
        $this->cacheResolver = $cacheResolver;
        $this->validator = $validator;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Returns the Image with the given $imageId or throws exception if no Image exists with $imageId.
     * 
     * @param int $imageId
     * @return Image
     * @throws NotFoundEntityException
     */
    public function getImageOr404($imageId) {
        $image = $this->em->getRepository('CoreMediaBundle:Image')->find($imageId);
        if (false === $image instanceof Image) {
            throw new NotFoundEntityException('media.not_found');
        }

        return $image;
    }

    /**
     * Creates and saves an Image from the $parameters and $file. If validation error occurs
     * then throws exception with the errors in it. If the user doesn't have profile picture 
     * and this image isn't private then set it as profile.
     * 
     * @param array $parameters
     * @param UploadedFile $file
     * @return Image
     * @throws InvalidFormException
     */
    public function createImageOr400(array $parameters, UploadedFile $file) {
        $form = $this->formFactory->create($this->uploadImageType);
        $form->submit($parameters);
        if ($form->isValid()) {
            $image = $form->getData();
            $image->setFile($file);
            if (false === $image->getOwner()->hasProfileImage() && false === $image->getIsPrivate()) {
                $image->setIsProfile(true);
            }

            $this->em->persist($image);
            $this->em->flush();

            return $image;
        }

        throw new InvalidFormException($form->getErrors(true));
    }

    /**
     * Returns an array with the required parameters for igorw file server according
     * to $image, $type and $size. The array also contains the path to the image under url key.
     * 
     * @param Image $image
     * @param int $type
     * @param int $size
     * @return array
     */
    public function getParamsForImageServing(Image $image, $type, $size) {
        $fullImageName = $image->getName();
        $imageName = substr($fullImageName, 0, -4);
        $imageExtension = substr($fullImageName, -4);
        $userHash = $this->hashIds->encode($image->getOwner()->getId());

        $returnArray = array(
            'format' => 'image/' . substr($imageExtension, -3),
            'options' => array('serve_filename' => 'temp' . $imageExtension),
        );

        switch ($type) {
            case ApiImageController::IMAGE_SERVING_TYPE_THUMBNAIL:
                $returnArray['url'] = 'users/' . $userHash . '/preview_cache/' . $imageName . '_' . $size . $imageExtension;
                return $returnArray;
            case ApiImageController::IMAGE_SERVING_TYPE_NORMAL:
            default:
                $returnArray['url'] = 'users/' . $userHash . '/images/' . $fullImageName;
                return $returnArray;
        }
    }

    /**
     * Returns a redirect response to the given $image's thumbnail. If $request contains
     * size paramter then sets to that the size of the thumbnail.
     * 
     * @param Image $image
     * @param int $userId
     * @param Request $request
     * @return redirect response
     */
    public function serveImageThumbnail(Image $image, $userId, Request $request) {
        $userHash = $this->hashIds->encode($userId);
        $filter = self::IMAGE_PREVIEW;

        $this->cacheResolver->setImageId($image->getId());
        if (is_numeric($request->query->get('size'))) {
            $this->mediaHelper->setPreviewImageSizeForServing($request->query->get('size'), $filter);
        }

        return $imagemanagerResponse = $this->liip
                ->filterAction(
                $request, // http request
                'users/' . $userHash . '/images/' . $image->getName(), // original image you want to apply a filter to
                $filter // filter defined in config.yml
        );
    }

    /**
     * Removes the given $image and it's caches.
     * 
     * @param Image $image
     * @return Image
     */
    public function removeImage(Image $image) {
        $this->em->remove($image);

        $userHash = $this->hashIds->encode($image->getOwner()->getId());
        $this->cacheManager->remove('users/' . $userHash . '/preview_cache/' . $image->getName(), 'image_preview');

        $this->em->flush();

        return $image;
    }

    /**
     * Sets the $image's $property property to $data. If validation fails then returns
     * error message, returns the Image otherwise.
     * 
     * @param Image $image
     * @param string $property
     * @param string $data
     * @return Image|string
     * @throws NotFoundPropertyException
     * @throws EntityValidationException
     */
    public function editImageData(Image $image, $property, $data) {
        switch ($property) {
            case Image::FIELD_ABOUT:
                $image->setAbout($data);
                break;
            case Image::FIELD_IS_PRIVATE:
                $image->setIsPrivate($data);
                break;
            case Image::FIELD_IS_PROFILE:
                $image->setIsProfile($data);
                $image->getOwner()->setEveryOtherImageNotProfileIfThisIs($image);
                break;
            default:
                throw new NotFoundPropertyException('media.edit.invalid_property');
        }

        $violationList = $this->validator->validate($image, null, array('upload'));
        if ($violationList->count() > 0) {
            throw new EntityValidationException($violationList);
        }

        $this->em->persist($image);
        $this->em->flush();

        return $image;
    }

}
