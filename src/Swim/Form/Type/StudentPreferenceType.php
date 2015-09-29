<?php

namespace Swim\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Validator\Constraints as Assert;

class StudentPreferenceType extends AbstractType
{

    protected $formOptions;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add('date', 'choice', array(
                'constraints' => new Assert\NotBlank(),
                'choice_list' => new ChoiceList(
                    $this->formOptions['dates'],
                    $this->formOptions['dates']
                ),
                'label' => 'Select Date',
                ))
            ->add('time', 'choice', array(
                'constraints' => new Assert\NotBlank(),
                'choice_list' => new ChoiceList(
                    $this->formOptions['times'],
                    $this->formOptions['times']
                ),
                'label' => 'Select Time',
                'expanded' => true,

            ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Swim\Entity\StudentPreference'
        ));
    }

    public function getName()
    {
        return 'schedule_pereference';
    }

    public function setFormOptions($options)
    {
        $this->formOptions = $options;
    }
}
