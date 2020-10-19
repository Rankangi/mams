<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        // Création de 3 à 5 Catégories
        $faker = Factory::create('fr_FR');

        for ($j = 1; $j <= mt_rand(3,5); $j++) {
            $category = new Category();
            $category->setTitle($faker->sentence())
                ->setDescription($faker->paragraph());

            $manager->persist($category);

            // Création de 5 à 10 articles
            for($i = 1; $i <= mt_rand(5,10); $i++){
                $article = new Article();

                $content = '<p>' . join($faker->paragraphs(3), '</p><p>') . '</p>';

                $article->setTitle($faker->sentence(4))
                    ->setDescription($faker->paragraph())
                    ->setContent($content)
                    ->setImage("http://placehold.it/700x400")
                    ->setAmount(mt_rand(0,10))
                    ->setPrice($faker->randomFloat(2,5,30))
                    ->setCategory($category);

                $manager->persist($article);
            }
        }

        $manager->flush();
    }
}
