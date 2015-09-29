<?php

namespace Swim\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// use Symfony\Component\Validator\Constraints as Assert;

class HostRegistrationType extends AbstractType
{
    protected $step = 1;
    protected $formOptions;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch($this->step) {
            case 1:
                $builder
                    ->add('user', new UserType())
                    ->add('address', new AddressType())
                    ->add('next', 'submit');
                break;
            case 2:
                $builder
                    ->add('students','collection', array(
                        'type' => new StudentType(),
                        'allow_add' => true,
                        'allow_delete' => true,
                        'prototype' => true,
                        'by_reference' => false,
                    ))
                    ->add('next', 'submit');
                break;
            case 3:
                $type = new StudentPreferenceType();
                $type->setFormOptions($this->formOptions);
                $builder
                    ->add('preferences', 'collection', array(
                        'type' => $type,
                        ))
                    ->add('next', 'submit');
                break;
            case 4:
                // $type = new PoolType();
                // $type->setFormOptions($this->formOptions);
                $builder
                    ->add('pool',  new PoolType())
                    ->add('next', 'submit');
                break;
            case 5:
                // $type = new PoolType();
                // $type->setFormOptions($this->formOptions);
                $builder
                    ->add('payment',  new PaymentType())
                    ->add('next', 'submit');
                break;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Swim\Entity\HostRegistration',
        ));
    }

    public function setStep($step)
    {
        $this->step = $step;
    }

    public function setFormOptions($options)
    {
        $this->formOptions = $options;
    }

    public function getName()
    {
        return 'hostregistration';
    }
}
