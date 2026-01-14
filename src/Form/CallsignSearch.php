<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CallsignSearch extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('callsign', TextType::class, array(
                'label' => false,
                'required' => true,
                'row_attr' => array(
                    'data-controller' => 'autocomplete',
                    'data-autocomplete-url-value' => '/api/callsigns'
                ),
                'attr' => array(
                    'placeholder' => 'searchform.placeholder',
                    'class' => 'form-control me-2',
                    'autofocus' => true,
                    'autocomplete' => 'off',
                    'data-autocomplete-target' => 'input'
                )))
            ->add('save', SubmitType::class, array(
                'label' => 'searchform.submit',
                'attr' => array(
                    'class' => 'btn btn-primary'
                )
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
