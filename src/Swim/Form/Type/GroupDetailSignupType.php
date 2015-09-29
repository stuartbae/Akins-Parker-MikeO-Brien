<?php

namespace Swim\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;


class GroupDetailSignupType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('maxgroup', 'choice', array(
                'constraints' => new Assert\NotBlank(),
                'choice_list' => new ChoiceList(
                    array(1, 2, 3, 4, 5, 6, 7),
                    array('1 Group','2 Groups', '3 Groups', '4 Groups', '5 Groups', '6 Groups', '7 Groups')
                ),
                'label' => 'How many groups of 4 would you like to host?',
                ))
            ->add('group_fill', 'choice', array(
                'constraints' => new Assert\NotBlank(),
                'choice_list' => new ChoiceList(
                    array('all', 'some', 'none'),
                    array('All', 'Some', 'None')),
                'label' => 'I prefer to fill ___________ of the available spots in my group',
                'expanded' => ture
                ))
            ->add('addition', 'choice', array(
                'constraints' => new Assert\NotBlank(),
                'choices' => array(1 => 'Yes', 0 => 'No'),
                'expanded' => true,
                'label' => 'Are you willing to add additional spaces at your pool if all sports are filled and there is additional interest?',
                ))
            ->add('term', 'checkbox', array(
                'label' => 'I understand that anyone invited as a guest to my group must register at least one month prior to class start date or any open spots will be filled from our placement list.',
                'mapped' => false,
                ))
            ->add('pool', new PoolType(), array(
                'label' => 'Pool Details',
                ))
            ->add('next', 'submit');

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Swim\Entity\GroupDetail',
            // 'validation_groups' => array(''),
            // 'cascade_validation' => true
        ));
    }


    public function getName()
    {
        return 'groupsignup';
    }
}
