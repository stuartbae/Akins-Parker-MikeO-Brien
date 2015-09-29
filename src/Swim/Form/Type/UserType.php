<?php

namespace Swim\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'label' => 'First',
            ))
            ->add('lastname', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'label' => 'Last',

            ))
            ->add('spouseFirstname', 'text', array(
                'label' => 'First',
                'required' => false,
            ))
            ->add('spouseLastname', 'text', array(
                'label' => 'Last',
                'required' => false,
            ))
            ->add('mobile', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'label' => 'Mobile Phone',
            ))
            ->add('home', 'text', array(
                'label' => 'Home Phone',
                'required' => false,
            ))
            ->add('address', new AddressType())
            ->add('email', 'email', array(
                'constraints' => new Assert\Email(),
            ))
            ->add('password', 'repeated', array(
               'constraints' => new Assert\Length(array('min' => 6)),
               'type'            => 'password',
               'invalid_message' => 'The password fields must match.',
               'first_options'   => array('label' => 'Enter Password'),
               'second_options'  => array('label' => 'Confirm Password'),
           ));
    }

    public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'data_class' => 'Swim\Entity\User'
            ));
        }

    public function getName()
    {
        return 'user';
    }
}
