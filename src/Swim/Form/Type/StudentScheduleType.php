<?php

namespace Swim\Form\Type;

use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class StudentScheduleType extends AbstractType
{
    protected $app;

    function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('schedulePrefs','collection', array(
                'type' => new \Swim\Form\Type\OpenGroupType($this->app),
            ))
            ->add('next', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            // 'data_class' => 'Swim\Entity\Student',
            'data_class' => 'Swim\Entity\Student',
            // 'validation_groups' => array(''),
            // 'cascade_validation' => true
        ));
    }


    public function getName()
    {
        return 'student_schedule';
    }
}
