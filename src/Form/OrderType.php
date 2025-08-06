<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', null, [
                'required'=>'true',
                'attr'=>['class'=>'form form-control mb-2 mt-1', 'placeholder'=>'First name']
            ])
            ->add('lastName', null, [
                'required'=>'true',
                'attr'=>['class'=>'form form-control mb-2 mt-1', 'placeholder'=>'Last name']
            ])
            ->add('phone', null, [
                'required'=>'true',
                'attr'=>['class'=>'form form-control mb-2 mt-1', 'placeholder'=>'Phone number']
            ])
            ->add('address', null, [
                'required'=>'true',
                'attr'=>['class'=>'form form-control mb-2 mt-1', 'placeholder'=>'Address']
            ])
            // ->add('createdAt', null, [
            //     'widget' => 'single_text',
            // ])
            ->add('city', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'name',
                'attr'=>['class'=>'form form-control mb-2 mt-1']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
