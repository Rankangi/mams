<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ArticleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title')->setLabel('Titre'),
            IntegerField::new('category',"Catégorie"),
            TextEditorField::new('description')->hideOnIndex()->setLabel('Description'),
            IntegerField::new('amount')->formatValue(function ($value){
                if ($value <= 5 and $value > 0){
                    return "Attention !!! " . $value;
                } else if ($value == 0){
                    return "Produit épuisé !!!";
                } else{
                    return $value;
                }
            })->setLabel('Quantité'),
            // TODO: Problème avec l'image qui est stockée comme un lien.
//            ImageField::new('image'),
            MoneyField::new('price')->setCurrency('EUR')->setLabel('Prix'),
        ];
    }
}
