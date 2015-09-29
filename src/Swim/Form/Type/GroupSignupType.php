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
    protected $open_groups;
    protected $students;

    function __construct(Application $app, ArrayCollection $students ) {
        $this->app = $app;
        $this->students = $students;
        $rows = $app['repository.group']->findAllOpen(100);
        foreach ($rows as $key => $group) {

            //     $day = date('F j', $value['starts_at']);
            //     $time = date('l g A', $value['starts_at']);
            $this->open_groups[$group['group_id']] = date('m/j l h a' ,$group['starts_at']);
        }
        // dump($rows, $this->open_groups);exit();
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->students->toArray() as $student) {
            // dump($builder);
            $builder = $builder
                ->add($student->getStudentId(),'choice', array(
                    'constraints' => new Assert\Choice(array(
                        'choices' => array_keys($this->open_groups),
                        'multiple' => true,
                        'min' => 1)),
                    'choices' => $this->open_groups,
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
