<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Form\ImageType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ArticleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            TextField::new('title')->setLabel('Titre'),
            AssociationField::new('category',"Catégorie"),
            TextEditorField::new('description')->hideOnIndex()->setLabel('Description'),
            TextEditorField::new('content')->hideOnIndex()->setLabel('Contenu'),
            IntegerField::new('amount')->formatValue(function ($value){
                if ($value <= 5 and $value > 0){
                    return "Attention !!! " . $value;
                } else if ($value == 0){
                    return "Produit épuisé !!!";
                } else{
                    return $value;
                }
            })->setLabel('Quantité'),
            ImageField::new('imageFile')->setFormType(VichImageType::class)->hideOnIndex()->hideOnDetail(),
            ImageField::new('image')->setBasePath($this->getParameter('app.path.article_images'))->hideOnForm(),
        ];

        if ($pageName == Crud::PAGE_NEW || $pageName == Crud::PAGE_EDIT){
            $fields[] = CollectionField::new("images")
                ->setEntryType(ImageType::class)
                ->onlyOnForms();
        }

        if ($pageName == Crud::PAGE_DETAIL){
            $fields[] = CollectionField::new("images")
                ->setTemplatePath("images.html.twig")
                ->onlyOnDetail();
        }

        $fields[] = MoneyField::new('price')->setCurrency('EUR')->setLabel('Prix');

        return $fields;

    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityLabelInSingular('Article')
            ->setEntityLabelInPlural('Articles');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Crud::PAGE_INDEX, "detail");
    }
}
