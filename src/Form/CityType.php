<?php

namespace App\Form;

use App\Entity\City;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'required'=>'true',
                'label'=>'Name of the city', 
                'attr'=>['class'=>'form form-control mb-2 mt-1', 'placeholder'=>'Name of the city']
            ])
            ->add('shippingCost', null, [
                'required'=>'true',
                'label'=>'Shipping cost', 
                'attr'=>['class'=>'form form-control mb-2 mt-1', 'placeholder'=>'Shipping cost']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => City::class,
        ]);
    }
}
