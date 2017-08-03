<?php

namespace Core\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Core\UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class RegistrationType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('birthDate', 'birthday', array(
                    'widget' => 'choice',
                    'format' => \IntlDateFormatter::FULL,
                    'years' => range(date('Y') - 18, date('Y') - 120),
                    'placeholder' => array(
                        'year' => 'form.birth_date.year', 'month' => 'form.birth_date.month', 'day' => 'form.birth_date.day',
                    ),
                    'label' => 'form.birth_date.label',
                    'empty_data' => null,
                    'invalid_message' => 'user.birth_date.date_time',
                    'translation_domain' => 'registration',
                ))
                ->add('plainPassword', 'repeated', array(
                    'type' => 'password',
                    'translation_domain' => 'registration',
                    'first_options' => array('label' => 'form.password.first.label'),
                    'second_options' => array('label' => 'form.password.second.label'),
                    'invalid_message' => 'user.password.not_match',
                    'empty_data' => null,
                ))
                ->add('address', 'entity', array(
                    'placeholder' => 'form.address.label',
                    'translation_domain' => 'registration',
                    'label' => 'form.address.label',
                    'class' => 'CoreCommonBundle:Address',
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository->createQueryBuilder('a')
                                ->orderBy('a.settlement', 'ASC');
                    },
                        )
                )
                ->add('gender', 'choice', array(
                    'expanded' => true,
                    'label' => 'form.gender.label',
                    'choices' => array(
                        User::GENDER_MALE => 'form.gender.male',
                        User::GENDER_FEMALE => 'form.gender.female'
                    ),
                    'empty_data' => null,
                    'translation_domain' => 'registration'
        ));
    }

    public function getParent() {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }

    public function getName() {
        return 'core_user_registration';
    }

}
