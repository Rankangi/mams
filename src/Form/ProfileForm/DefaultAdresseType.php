<?php

namespace App\Form\ProfileForm;

use App\Entity\Adresse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DefaultAdresseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street', TextType::class, ["attr" => ['placeholder' => 'Adresse', 'class' => 'form-control']])
            ->add('city', TextType::class, ["attr" => ['placeholder' => 'Ville', 'class' => 'form-control']])
            ->add('codePostal', TextType::class, ["attr" => ['placeholder' => 'Code Postal', 'class' => 'form-control']])
            ->add('country', TextType::class, ["attr" => ['placeholder' => 'Pays', 'class' => 'form-control']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Adresse::class,
        ]);
    }
}
