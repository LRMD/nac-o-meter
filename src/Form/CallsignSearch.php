<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CallsignSearch extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('POST')
            ->add('callsign', TextType::class, array(
                'label' => false,
                'attr' => array(
                    'placeholder' => 'searchform.placeholder',
                    'class' => 'form-control me-2',
                    'autofocus' => true
                )))
            ->add('save', SubmitType::class, array(
                'label' => 'searchform.submit',
                'attr' => array(
                    'class' => 'btn btn-primary'
                )
            ))
        ;
    }
}
