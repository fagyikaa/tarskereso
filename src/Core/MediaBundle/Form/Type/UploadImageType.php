<?php

namespace Core\MediaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class UploadImageType extends AbstractType {
    
    protected $tokenStorage;
    
    public function __construct(TokenStorageInterface $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $builder
                ->add('isPrivate', 'checkbox', array(
                    'label' => 'upload_image.form.is_private.label',
                    'translation_domain' => 'gallery',
                    'required' => false,
                ))
                ->add('isProfile', 'checkbox', array(
                    'label' => 'upload_image.form.is_profile.label',
                    'translation_domain' => 'gallery',
                    'required' => false,
                ))
                ->add('about', 'textarea', array(
                    'label' => 'upload_image.form.about.label',
                    'translation_domain' => 'gallery',
                    'required' => false,
        ));
        
        //Set the underlying Images's owner to the current user.
        $setCurrentUser = function(FormEvent $event) {
            $image = $event->getForm()->getData();
            $image->setOwner($this->tokenStorage->getToken()->getUser());
        };
        
        $builder->addEventListener(FormEvents::POST_SUBMIT, $setCurrentUser);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Core\MediaBundle\Entity\Image',
            'intention' => 'core_media_image',
            'validation_groups' => array('upload'),
        ));
    }

    public function getName() {
        return 'core_media_image';
    }

}
