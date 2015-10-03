<?php

namespace Swim\Form\Type;

use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Swim\Entity\StudentCollection as StudentGroup;
use Doctrine\Common\Collections\ArrayCollection;


class GroupSignupType extends AbstractType
{
    protected $app;
    // protected $group_starts_at;
    // protected $group_id;
    protected $students;

    function __construct(Application $app, ArrayCollection $students ) {
        $this->app = $app;
        $this->students = $students;

        // dump($rows, $this->open_groups);exit();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $rows = $this->app['repository.group']->findAllOpen(100);
        foreach ($rows as $key => $group) {
            $open_groups[$group['group_id']] = date('m/j D h:i a' ,$group['starts_at']);
            $label = date('m-d-Y', $group['starts_at']);
            $id = date('m_d', $group['starts_at']);
            $open_days [$id] = $label;
        }
        $open_days = array_unique($open_days);
        foreach ($this->students->toArray() as $student) {
            // dump($builder);
            $builder = $builder
                ->add('pref1', 'choice', array(
                    'choices' => $open_days,
                    'mapped' => false,
                    'label' => 'Preference 1',
                    ))
                ->add($student->getStudentId(),'choice', array(
                    'constraints' => new Assert\Choice(array(
                        'choices' => array_keys($open_groups),
                        'multiple' => true,
                        'min' => 1)),
                    'choices' => $open_groups,
                    'multiple' => true,
                    'expanded' => true,
                    'mapped' => true,
                    'label' => $student->getName()
                ))
                ->add('pref2', 'choice', array(
                    'choices' => $open_days,
                    'mapped' => false,
                    'label' => 'Preference 2',
                    ))
                ->add($student->getStudentId(),'choice', array(
                    'constraints' => new Assert\Choice(array(
                        'choices' => array_keys($open_groups),
                        'multiple' => true,
                        'min' => 1)),
                    'choices' => $open_groups,
                    'multiple' => true,
                    'expanded' => true,
                    'mapped' => true,
                    'label' => $student->getName()
                ))
                ->add('pref3', 'choice', array(
                    'choices' => $open_days,
                    'mapped' => false,
                    'label' => 'Preference 3',
                    ))
                ->add($student->getStudentId(),'choice', array(
                    'constraints' => new Assert\Choice(array(
                        'choices' => array_keys($open_groups),
                        'multiple' => true,
                        'min' => 1)),
                    'choices' => $open_groups,
                    'multiple' => true,
                    'expanded' => true,
                    'mapped' => true,
                    'label' => $student->getName()
                ));
                // ->add('temp_student_id', 'hidden', array('data' => $student->getStudentId()));
        }
            $builder = $builder->add('next', 'submit');
        // dump($builder);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // $resolver->setDefaults(array(
        //     'data_class' => 'Swim\Entity\StudentCollection',
        //     // 'validation_groups' => array(''),
        //     // 'cascade_validation' => true
        // ));
    }


    public function getName()
    {
        return 'groupsignup';
    }
}
