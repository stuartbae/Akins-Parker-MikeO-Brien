<?php

namespace Swim\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street', 'text', array(
                // 'constraints' => new Assert\NotBlank(),
                'label' => 'Street Address',
            ))
            ->add('city', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'label' => 'City',
            ))
            ->add('state', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'label' => 'State',
            ))
            ->add('zip', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'label' => 'Zip Code',
            ));

    }

    public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'data_class' => 'Swim\Entity\Address'
            ));
        }
    public function getName()
    {
        return 'address';
    }
}
