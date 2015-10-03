<?php

namespace Swim\Form\Type;

use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Validator\Constraints as Assert;

class StudentType extends AbstractType
{

    protected $app;
    protected $level_id;
    protected $level;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $rows = $this->app['repository.helper']->findAllLevel();
        // dump($rows);exit();
        foreach ($rows as $key => $level) {
            // dump($level);
            $this->level[] = $level['level'];
            $this->level_id[] = $level['level_id'];
        }
        // dump($this->level_id);
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'label' => 'Student Name',

            ))
            ->add('birthdate', 'birthday', array(
                 'constraints' => new Assert\Date(),
                 'widget' => 'choice',
                 'format' => 'MM / dd / yyyy',
                 'years' => array_reverse(range(2002, date('Y'))),
            ))
            ->add('level', 'choice', array(
                'constraints' => new Assert\NotBlank(),
                'choice_list' => new ChoiceList($this->level_id, $this->level),
                'label' => 'Experience'
            ))
            ->add('note', 'textarea', array(
                'attr' => array(
                    'rows' => '8',
                ),
                'label' => 'Does the Student have any special needs?',
            ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Swim\Entity\Student'
        ));
    }

    public function getName()
    {
        return 'student';
    }
}
