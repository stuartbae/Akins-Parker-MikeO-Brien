<?php

namespace Swim\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PoolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address', new AddressType())
            ->add('file', 'file', array(
                'required' => FALSE,
                'label' => 'Click Choose File to add a new image.',
            ))
            ->add('accessinfo', 'textarea', array(
                'label' => 'Please provide any pool access info that would be helpful to participants',
                'attr' => array('placeholder' =>
                    '(Gated community access, how you would like people to enter pool area, etc.)',
                    'rows' => '10'),
                'required' => false,
            ));

    }

    public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'data_class' => 'Swim\Entity\Pool'
            ));
        }
    public function getName()
    {
        return 'address';
    }
}
