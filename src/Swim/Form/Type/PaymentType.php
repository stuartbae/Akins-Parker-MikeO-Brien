<?php

namespace Swim\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

            $builder
            // ->add('quantity', 'number', array(
            //     'constraints' => new Assert\NotBlank(),
            //     'label' => 'Quantity',
            //     'readonly' => true,
            //     'data' => 3,
            // ))
            ->add('fullpay', 'checkbox', array(
                'label' => 'I would like to pay in full to take advantage of the discount',
                'required' => false,
                ))
            ->add('coupon', 'text', array(
                // 'constraints' => new Assert\NotBlank(),
                'required' => false,
                'label' => 'Coupon Code',
            ))
            ->add('address', new AddressType(), array(
                'label' => 'Billing Address'
                ))
            ->add('card_number', 'text', array(
                'constraints' => new Assert\CardScheme(array(
                    'schemes' => array('AMEX', 'DISCOVER', 'MASTERCARD', 'VISA')
                    )),
                'label' => 'Card Number',
                ))
            ->add('exp_month', 'choice', array(
                'constraints' => new Assert\NotBlank(),
                'choice_list' => new ChoiceList(range(1,12), range(1,12)),
                'placeholder' => 'Month',
                'label' => 'Exp month',
                ))
            ->add('exp_year', 'choice', array(
                'constraints' => new Assert\NotBlank(),
                'choice_list' => new ChoiceList(range((int) date('Y'), (int) date('Y')+10), range((int)date('Y'), (int) date('Y')+10)),
                'placeholder' => 'Exp year',
                'label' => '',
                ))
            ->add('card_ccv', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'label' => 'Security Code',
            ))
            ->add('card_name', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'label' => 'Cardholder Name',
            ))
            ->add('terms', 'checkbox', array(
                'label' => 'I have read and agree to the Terms of Service',
                ))
            ->add('submit', 'submit');

    }

    public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'data_class' => 'Swim\Entity\Payment'
            ));
        }
    public function getName()
    {
        return 'payment';
    }
}
