<?php

namespace App\Form;

use App\Entity\Adresse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdresseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, ["attr" => ['placeholder' => 'Nom', 'class' => 'form-control']])
            ->add('lastName', TextType::class, ["attr" => ['placeholder' => 'Prénom', 'class' => 'form-control']])
            ->add('street', TextType::class, ["attr" => ['placeholder' => 'Adresse', 'class' => 'form-control']])
            ->add('city', TextType::class, ["attr" => ['placeholder' => 'Ville', 'class' => 'form-control']])
            ->add('codePostal', TextType::class, ["attr" => ['placeholder' => 'Code Postale', 'class' => 'form-control']])
            ->add('country', TextType::class, ["attr" => ['placeholder' => 'Pays', 'class' => 'form-control']])
            ->add('differentBillingAddress', CheckboxType::class, ['label' => "Adresse de facturation différente.", 'mapped' => false, 'required' => false])
            ->add('sessionId', HiddenType::class, ['mapped' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Adresse::class,
        ]);
    }
}
