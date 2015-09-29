<?php

namespace Swim\Form\Type;

use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class StudentSignupType extends AbstractType
{
    protected $app;

    function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('students','collection', array(
                'type' => new StudentType($this->app),
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
            ))
            ->add('next', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Swim\Entity\StudentCollection',
            // 'validation_groups' => array(''),
            // 'cascade_validation' => true
        ));
    }


    public function getName()
    {
        return 'studentsignup';
    }
}
