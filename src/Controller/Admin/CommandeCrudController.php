<?php

namespace App\Controller\Admin;

use App\Entity\Commande;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Vich\UploaderBundle\Form\Type\VichFileType;

class CommandeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Commande::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            DateField::new('date')->hideOnForm(),
            AssociationField::new('article')->hideOnForm(),
            IntegerField::new('amount', 'Quantité')->hideOnForm(),
            AssociationField::new('user', 'Acheteur')->hideOnForm(),
            AssociationField::new('shippingAddress', 'Adresse d\'envoie')->hideOnForm(),
            ChoiceField::new('statut')->setChoices(["Payée"=> "Payée", "Expédiée"=> "Terminée"]),
            TextField::new('Facture')->hideOnForm(),
            UrlField::new('facturePath','Téléchargement')->formatValue(function (){
                return "Télécharger";
            })->hideOnForm()

        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Commandes');
    }
}
