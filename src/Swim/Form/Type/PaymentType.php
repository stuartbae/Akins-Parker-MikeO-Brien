<?php

namespace Swim\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

            $builder
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
                'choice_list' => new ChoiceList( range(1, 12), range(1,12) ),
                'placeholder' => 'Month',
                'label' => 'Exp Month',
            ))
            ->add('exp_year', 'choice', array(
                'constraints' => new Assert\NotBlank(),
                'choice_list' => new ChoiceList(
                    range( (int) date('Y'), (int) date('Y')+10 ),
                    range( (int) date('Y'), (int) date('Y')+10) ),
                'placeholder' => 'Year',
                'label' => 'Exp Year',
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


class CreditCardExpirationDateType extends AbstractType
{
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ('choice' == $options['widget']) {
            if (empty($view['day']->vars['value'])) {
                $view['day']->vars['value'] = $view['day']->vars['choices'][0]->value;
            }

            $style = 'display:none';
            if (false == empty($view['day']->vars['attr']['style'])) {
                $style = $view['day']->vars['attr']['style'].'; '.$style;
            }

            $view['day']->vars['attr']['style'] = $style;
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->replaceDefaults(array(
            'years' => range(date('Y'), date('Y') + 9)
        ));
    }

    public function getParent()
    {
        return 'date';
    }

    public function getName()
    {
        return 'payum_credit_card_expiration_date';
    }
}


class IncompleteDateTransformer implements DataTransformerInterface
{
/**
 * Do nothing when transforming from norm -> view
 */
public function transform($object)
{
    return $object;
}

/**
 * If some components of the date is missing we'll add those.
 * This reverse transform will work when month and/or day is missing
 *
 */
public function reverseTransform($date)
{
    if (!is_array($date)) {
        return $date;
    }

    if (empty($date['year'])) {
        return $date;
    }

    if (empty($date['day'])) {
        $date['day']=1;
    }

    if (empty($date['month'])) {
        $date['month']=1;
    }

    return $date;
}
}
