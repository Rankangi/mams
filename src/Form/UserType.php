<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, ['attr' => ['class' => 'form-control form-control-alternative']])
            ->add('firstName', TextType::class, ['attr' => ['class' => 'form-control form-control-alternative']])
            ->add('lastName', TextType::class, ['attr' => ['class' => 'form-control form-control-alternative']])
            ->add('number', TelType::class, ['attr' => ['class' => 'form-control form-control-alternative']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
