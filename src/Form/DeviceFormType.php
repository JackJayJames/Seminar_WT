<?php

namespace App\Form;

use App\Entity\Device;
use App\Entity\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DeviceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Název zařízení',
                'attr' => [
                    'placeholder' => 'Zadejte název zařízení',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Název zařízení je povinný.',
                    ]),
                ],
            ])
            ->add('type', EntityType::class, [
                'class' => Type::class,
                'choice_label' => 'name', // Zobrazuje pole "name" z entity Type
                'label' => 'Typ zařízení',
                'placeholder' => 'Vyberte typ zařízení', // Defaultní prázdná volba
                'attr' => [
                    'class' => 'form-select',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Typ zařízení je povinný.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Device::class,
        ]);
    }
}
