<?php

namespace Swim\Form\Type;

use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Swim\Entity\StudentCollection as StudentGroup;
use Doctrine\Common\Collections\ArrayCollection;


class OpenGroupType extends AbstractType
{
    protected $app;
    protected $student;

    function __construct(Application $app, $student ) {
        $this->app = $app;
        $this->student = $student;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('day1', 'choice', array(
                'choices' => $this->getOpenGroups()['days'],
                'mapped' => false,
                'label' => 'Preferene I'
                ))
            ->add($this->student->getStudentId().'pref1', 'choice', array(
                'choices' => $this->getOpenGroups2(),
                'expanded' => true,
                'multiple' => true,
                'mapped' => false,
                'label' => false,
                'choice_label' =>function ($allChoices, $currentChoiceKey) {
                        // dump($allChoices, $currentChoiceKey);exit();
                        return date('l h:i A', $currentChoiceKey);
                        },
                'choice_attr' => function ($allChoices, $currentChoiceKey) {
                        return array('class' => 'class_'. date('m_d', $currentChoiceKey));
                        },

             ))
            ->add('day2', 'choice', array(
                'choices' => $this->getOpenGroups()['days'],
                'mapped' => false,
                'label' => 'Preferene II'
                ))
            ->add($this->student->getStudentId().'pref2', 'choice', array(
                            'choices' => $this->getOpenGroups2(),
                            'expanded' => true,
                            'multiple' => true,
                            'mapped' => false,
                            'label' => false,
                            'choice_label' =>function ($allChoices, $currentChoiceKey) {
                                    // dump($allChoices, $currentChoiceKey);exit();
                                    return date('l h:i A', $currentChoiceKey);
                                    },
                            'choice_attr' => function ($allChoices, $currentChoiceKey) {
                                    return array('class' => 'class_'. date('m_d', $currentChoiceKey));
                                    },

                         ))
            ->add('day3', 'choice', array(
                'choices' => $this->getOpenGroups()['days'],
                'mapped' => false,
                'label' => 'Preferene III'
                ))
            ->add($this->student->getStudentId().'pref3', 'choice', array(
                            'choices' => $this->getOpenGroups2(),
                            'expanded' => true,
                            'multiple' => true,
                            'mapped' => false,
                            'label' => false,
                            'choice_label' =>function ($allChoices, $currentChoiceKey) {
                                    // dump($allChoices, $currentChoiceKey);exit();
                                    return date('l h:i A', $currentChoiceKey);
                                    },
                            'choice_attr' => function ($allChoices, $currentChoiceKey) {
                                    return array('class' => 'class_'. date('m_d', $currentChoiceKey));
                                    },

                         ));


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // $resolver->setDefaults(array(
        //     'data_class' => 'Swim\Entity\StudentCollection',
        //     // 'validation_groups' => array(''),
        //     // 'cascade_validation' => true
        // ));
    }

    public function getOpenGroups()
    {
        $rows = $this->app['repository.group']->findAllOpen(100);
        foreach ($rows as $key => $group) {
            $open_groups[$group['group_id']] = date('D h:i a' ,$group['starts_at']);
            $label = date('m-d-Y', $group['starts_at']);
            $id = date('m_d', $group['starts_at']);
            $open_days [$id] = $label;
        }
        $open_days = array_unique($open_days);
        return array('days' => $open_days, 'groups' => $open_groups);
    }

    public function getOpenGroups2()
    {
        $rows = $this->app['repository.group']->findAllOpen(100);
        foreach ($rows as $key => $group) {
            $open_groups[$group['group_id']] = $group['starts_at'];
        }
        return $open_groups;
    }

    public function getName()
    {
        return 'open_group';
    }
}
