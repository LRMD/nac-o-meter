<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

class LogSubmitType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Calculate last Tuesday
        $now = new \DateTime();
        $lastTuesday = clone $now;
        $daysToSubtract = ($lastTuesday->format('N') - 2 + 7) % 7;
        $lastTuesday->modify("-{$daysToSubtract} days");
        
        // Get day of month for Tuesday
        $dayOfMonth = (int)$lastTuesday->format('j');
        
        // Determine default contest based on the Tuesday's date
        $defaultContest = null;
        if ($dayOfMonth <= 7) {
            $defaultContest = '144 MHz';
        } elseif ($dayOfMonth <= 14) {
            $defaultContest = '432 MHz';
        } elseif ($dayOfMonth <= 21) {
            $defaultContest = '1296 MHz';
        }
        
        $builder
            ->add('TName', ChoiceType::class, [
                'choices' => [
                    'NAC/LYAC 144 MHz' => '144 MHz',
                    'NAC/LYAC 432 MHz' => '432 MHz',
                    'NAC/LYAC 1296 MHz' => '1296 MHz',
                    'NAC/LYAC Microwave' => 'MICROWAVE'
                ],
                'required' => true,
                'label' => 'submit.form.contest',
                'placeholder' => 'Select contest band',
                'data' => $defaultContest,
                'attr' => ['class' => 'form-control']
            ])
            ->add('TDate', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'submit.form.date',
                'data' => $lastTuesday,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Contest date (Tuesday)'
                ]
            ])
            ->add('PCall', TextType::class, [
                'required' => true,
                'label' => 'submit.form.callsign',
                'attr' => [
                    'class' => 'form-control',
                    'pattern' => '[A-Za-z0-9/]+',
                    'placeholder' => 'Your callsign',
                    'title' => 'Valid callsign required'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Callsign is required']),
                    new Assert\Regex([
                        'pattern' => '/^[A-Za-z0-9\/]+$/',
                        'message' => 'Invalid callsign format'
                    ])
                ]
            ])
            ->add('PWWLo', TextType::class, [
                'required' => true,
                'label' => 'submit.form.wwl',
                'attr' => [
                    'class' => 'form-control',
                    'pattern' => '[A-Ra-r][A-Ra-r][0-9][0-9][A-Xa-x][A-Xa-x]',
                    'placeholder' => 'Your WWL locator (e.g. KO24AA)',
                    'title' => 'Valid WWL locator required'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'WWL locator is required']),
                    new Assert\Regex([
                        'pattern' => '/^[A-R][A-R][0-9][0-9][A-X][A-X]$/i',
                        'message' => 'Invalid WWL locator format'
                    ])
                ]
            ])
            ->add('PBand', ChoiceType::class, [
                'required' => true,
                'label' => 'submit.form.band',
                'choices' => [
                    '2.4 GHz' => '2.4 GHz',
                    '5.7 GHz' => '5.7 GHz',
                    '10 GHz' => '10 GHz',
                    '24 GHz' => '24 GHz'
                ],
                'placeholder' => 'Select band',
                'attr' => [
                    'class' => 'form-control',
                    'disabled' => 'disabled'
                ]
            ])
            ->add('PSect', TextType::class, [
                'required' => false,
                'data' => 'SINGLE',
                'label' => 'submit.form.section',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('RCall', TextType::class, [
                'required' => false,
                'label' => 'submit.form.manager',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Contest manager callsign'
                ]
            ])
            ->add('PClub', TextType::class, [
                'required' => false,
                'label' => 'submit.form.club',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Club name'
                ]
            ])
            ->add('RAdr1', TextType::class, [
                'required' => false,
                'label' => 'submit.form.address1',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Address line 1'
                ]
            ])
            ->add('RAdr2', TextType::class, [
                'required' => false,
                'label' => 'submit.form.address2',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Address line 2'
                ]
            ])
            ->add('RPoCo', TextType::class, [
                'required' => false,
                'label' => 'submit.form.postal',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Postal code'
                ]
            ])
            ->add('RCity', TextType::class, [
                'required' => false,
                'label' => 'submit.form.city',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'City'
                ]
            ])
            ->add('RCoun', TextType::class, [
                'required' => false,
                'label' => 'submit.form.country',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Country'
                ]
            ])
            ->add('RPhon', TextType::class, [
                'required' => false,
                'label' => 'submit.form.phone',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Phone number'
                ]
            ])
            ->add('RHBBS', TextType::class, [
                'required' => false,
                'label' => 'submit.form.bbs',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'BBS'
                ]
            ])
            ->add('MOpe1', TextType::class, [
                'required' => false,
                'label' => 'submit.form.operator1',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Main operator callsign'
                ]
            ])
            ->add('MOpe2', TextType::class, [
                'required' => false,
                'label' => 'submit.form.operator2',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Second operator callsign'
                ]
            ])
            ->add('STXEq', TextType::class, [
                'required' => false,
                'label' => 'submit.form.tx_equipment',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Transmitter equipment'
                ]
            ])
            ->add('SPowe', TextType::class, [
                'required' => false,
                'label' => 'submit.form.power',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Power'
                ]
            ])
            ->add('SRXEq', TextType::class, [
                'required' => false,
                'label' => 'submit.form.rx_equipment',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Receiver equipment'
                ]
            ])
            ->add('SAnte', TextType::class, [
                'required' => false,
                'label' => 'submit.form.antenna',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Antenna (e.g. 35W/IC-7000/13x el DK7ZB)'
                ]
            ])
            ->add('SAntH', TextType::class, [
                'required' => false,
                'label' => 'submit.form.antenna_height',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Antenna height'
                ]
            ])
            ->add('Remarks', TextareaType::class, [
                'required' => false,
                'label' => 'submit.form.remarks',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Additional comments'
                ]
            ])
            ->add('qsos', CollectionType::class, [
                'entry_type' => QsoType::class,
                'allow_add' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => false,
                'constraints' => [
                    new Assert\Count([
                        'min' => 1,
                        'minMessage' => 'submit.validation.qso_required'
                    ])
                ]
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['TDate']) && $data['TDate'] instanceof \DateTime) {
                if ($data['TDate']->format('N') !== '2') {
                    $form->addError(new FormError($this->translator->trans('submit.validation.tuesday')));
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'csrf_protection' => false
        ]);
    }
} 