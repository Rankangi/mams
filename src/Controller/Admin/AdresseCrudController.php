<?php

namespace App\Controller\Admin;

use App\Entity\Adresse;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AdresseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Adresse::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('firstName','Nom'),
            TextField::new('lastName','Prénom'),
            TextField::new('street','Adresse'),
            TextField::new('city','Ville'),
            TextField::new('codePostal','Code postal'),
            TextField::new('country', 'Pays'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Crud::PAGE_INDEX, Action::NEW)
            ->disable(Crud::PAGE_INDEX, Action::EDIT)
            ->disable(Crud::PAGE_INDEX, Action::DELETE)
            ->disable(Crud::PAGE_DETAIL, Action::DELETE)
            ->disable(Crud::PAGE_DETAIL, Action::EDIT)
            ->disable(Crud::PAGE_DETAIL, Action::INDEX);
    }
}
