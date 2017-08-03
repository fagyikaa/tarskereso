<?php

namespace Core\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Core\UserBundle\Entity\User;
use Core\UserBundle\Entity\UserPersonal;

class SearchType extends AbstractType {
    
    protected $em;
    
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $builder->add('hairColor', 'choice', array(
                    'expanded' => true,
                    'label' => 'form.hair_color.label',
                    'choices' => array(
                        UserPersonal::HAIR_COLOR_BLACK => 'form.hair_color.black',
                        UserPersonal::HAIR_COLOR_BLLUE => 'form.hair_color.blue',
                        UserPersonal::HAIR_COLOR_BLOND => 'form.hair_color.blond',
                        UserPersonal::HAIR_COLOR_BROWN => 'form.hair_color.brown',
                        UserPersonal::HAIR_COLOR_GRAY => 'form.hair_color.gray',
                        UserPersonal::HAIR_COLOR_GREEN => 'form.hair_color.green',
                        UserPersonal::HAIR_COLOR_OTHER => 'form.hair_color.other',
                        UserPersonal::HAIR_COLOR_PINK => 'form.hair_color.pink',
                        UserPersonal::HAIR_COLOR_RED => 'form.hair_color.red',
                    ),
                    'multiple' => true,
                    'empty_data' => null,
                    'translation_domain' => 'search',
                    'required' => false,
                ))
                ->add('hairLength', 'choice', array(
                    'expanded' => true,
                    'label' => 'form.hair_length.label',
                    'choices' => array(
                        UserPersonal::HAIR_LENGTH_BALD => 'form.hair_length.bald',
                        UserPersonal::HAIR_LENGTH_SHORT => 'form.hair_length.short',
                        UserPersonal::HAIR_LENGTH_MIDDLE => 'form.hair_length.middle',
                        UserPersonal::HAIR_LENGTH_LONG => 'form.hair_length.long',
                    ),
                    'multiple' => true,
                    'empty_data' => null,
                    'translation_domain' => 'search',
                    'required' => false,
                ))
                ->add('eyeColor', 'choice', array(
                    'expanded' => true,
                    'label' => 'form.eye_color.label',
                    'choices' => array(
                        UserPersonal::EYE_COLOR_BLUE => 'form.eye_color.blue',
                        UserPersonal::EYE_COLOR_BROWN => 'form.eye_color.brown',
                        UserPersonal::EYE_COLOR_GREEN => 'form.eye_color.green',
                        UserPersonal::EYE_COLOR_OTHER => 'form.eye_color.other',
                    ),
                    'multiple' => true,
                    'empty_data' => array(),
                    'translation_domain' => 'search',
                    'required' => false,
                ))
                ->add('bodyShape', 'choice', array(
                    'expanded' => true,
                    'label' => 'form.body_shape.label',
                    'choices' => array(
                        UserPersonal::BODY_SHAPE_AVERAGE => 'form.body_shape.average',
                        UserPersonal::BODY_SHAPE_CHUBBY => 'form.body_shape.chubby',
                        UserPersonal::BODY_SHAPE_MUSCULAR => 'form.body_shape.muscular',
                        UserPersonal::BODY_SHAPE_OBESE => 'form.body_shape.obese',
                        UserPersonal::BODY_SHAPE_SKINNY => 'form.body_shape.skinny',
                        UserPersonal::BODY_SHAPE_SPORT => 'form.body_shape.sport',
                    ),
                    'multiple' => true,
                    'empty_data' => array(),
                    'translation_domain' => 'search',
                    'required' => false,
                ))
                ->add('wantTo', 'choice', array(
                    'expanded' => true,
                    'label' => 'form.want_to.label',
                    'choices' => array(
                        UserPersonal::WANT_TO_FRIENDSHIP => 'form.want_to.friendship',
                        UserPersonal::WANT_TO_RELATIONSHIP => 'form.want_to.relationship',
                        UserPersonal::WANT_TO_SEX => 'form.want_to.sex',
                    ),
                    'multiple' => true,
                    'empty_data' => null,
                    'translation_domain' => 'search',
                    'required' => false,
                ))
                ->add('searchingFor', 'choice', array(
                    'expanded' => true,
                    'label' => 'form.searching_for.label',
                    'choices' => array(
                        UserPersonal::SEARCHING_FOR_BOTH => 'form.searching_for.both',
                        UserPersonal::SEARCHING_FOR_MAN => 'form.searching_for.man',
                        UserPersonal::SEARCHING_FOR_WOMAN => 'form.searching_for.woman',
                    ),
                    'multiple' => true,
                    'empty_data' => null,
                    'translation_domain' => 'search',
                    'required' => false,
                ))
                ->add('gender', 'choice', array(
                    'expanded' => true,
                    'label' => 'form.gender.label',
                    'choices' => array(
                        User::GENDER_MALE => 'form.gender.male',
                        User::GENDER_FEMALE => 'form.gender.female'
                    ),
                    'multiple' => true,
                    'empty_data' => null,
                    'translation_domain' => 'search',
                    'required' => false,
                ))
                ->add('address', 'entity', array(
                    'translation_domain' => 'search',
                    'required' => false,
                    'label' => 'form.address.label',
                    'class' => 'CoreCommonBundle:Address',
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository->createQueryBuilder('a')
                                ->orderBy('a.settlement', 'ASC');
                    },
                        )
                )
                ->add('county', 'choice', array(
                    'expanded' => false,
                    'label' => 'form.county.label',
                    'choices' => $this->em->getRepository('CoreCommonBundle:Address')->getCountyForAll(),
                    'multiple' => false,
                    'empty_data' => null,
                    'translation_domain' => 'search',
                    'required' => false,
                ))
                ->add('heightFrom', 'integer', array(
                    'label' => 'form.height.from_label',
                    'required' => false,
                    'translation_domain' => 'search',
                ))
                ->add('heightTo', 'integer', array(
                    'label' => 'form.height.to_label',
                    'required' => false,
                    'translation_domain' => 'search',
                ))
                ->add('weightFrom', 'integer', array(
                    'label' => 'form.weight.from_label',
                    'required' => false,
                    'translation_domain' => 'search',
                ))
                ->add('weightTo', 'integer', array(
                    'label' => 'form.weight.to_label',
                    'required' => false,
                    'translation_domain' => 'search',
                ))
                ->add('ageFrom', 'integer', array(
                    'label' => 'form.age.from_label',
                    'required' => false,
                    'translation_domain' => 'search',
                ))
                ->add('ageTo', 'integer', array(
                    'label' => 'form.age.to_label',
                    'required' => false,
                    'translation_domain' => 'search',
                ));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Core\UserBundle\Entity\UserSearch',
            'intention' => 'core_user_search',
        ));
    }

    public function getName() {
        return 'core_user_search';
    }

}
