<?php

namespace Core\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Core\UserBundle\Managers\RoleManager;
use Core\UserBundle\Form\DataTransformer\RolesToFilteredRolesTransformer;

class RoleSetType extends AbstractType {

    private $roleManager;
    private $roleHierarchy;
    
    public function __construct(RoleManager $roleManager, $roleHierarchy) {
        $this->roleManager = $roleManager;
        $this->roleHierarchy = $roleHierarchy;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder
                ->add('name')
                ->add('roles', 'collection', array(
                    'type' => 'choice',
                    'options' => array(
                        'choices' => $this->roleManager->getEveryRoleOfHierarchyForForm()
                    ),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'required' => true,
                    'data' => array(null),
        ));

        //Filters the selected roles (fake roles, child roles)
        $builder->get('roles')->addModelTransformer(new RolesToFilteredRolesTransformer($this->roleManager, $this->roleHierarchy));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Core\UserBundle\Entity\RoleSet'
        ));
    }

    public function getName() {
        return 'role_set';
    }

}
