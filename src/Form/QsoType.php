<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class QsoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => false,
                'attr' => [
                    'class' => 'form-control form-control-sm',
                    'readonly' => true
                ]
            ])
            ->add('time', TimeType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => false,
                'attr' => [
                    'class' => 'form-control form-control-sm',
                    'step' => '60'
                ]
            ])
            ->add('call', TextType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'class' => 'form-control form-control-sm',
                    'placeholder' => 'Callsign',
                    'pattern' => '[A-Za-z0-9\/]+',
                    'title' => 'Valid callsign required'
                ],
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[A-Za-z0-9\/]+$/',
                        'message' => 'Invalid callsign format'
                    ])
                ]
            ])
            ->add('mode', ChoiceType::class, [
                'choices' => [
                    'CW' => 'CW',
                    'SSB' => 'SSB',
                    'FM' => 'FM'
                ],
                'required' => true,
                'label' => false,
                'attr' => ['class' => 'form-control form-control-sm']
            ])
            ->add('sent', TextType::class, [
                'required' => true,
                'label' => false,
                'attr' => [
                    'class' => 'form-control form-control-sm',
                    'placeholder' => '599'
                ]
            ])
            ->add('rcvd', TextType::class, [
                'required' => true,
                'label' => false,
                'attr' => [
                    'class' => 'form-control form-control-sm',
                    'placeholder' => '599'
                ]
            ])
            ->add('wwl', TextType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'class' => 'form-control form-control-sm',
                    'pattern' => '[A-Ra-r][A-Ra-r][0-9][0-9][A-Xa-x][A-Xa-x]',
                    'placeholder' => 'KO24AA',
                    'title' => 'Valid WWL locator required'
                ],
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[A-R][A-R][0-9][0-9][A-X][A-X]$/i',
                        'message' => 'Invalid WWL locator format'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }
} 