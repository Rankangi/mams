<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        for($i = 1; $i <= 10; $i++){
            $article = new Article();
            $article->setTitle("Titre de l'article n°$i")
                    ->setDescription("Contenu de l'article n°$i")
                    ->setContent("Voici le contenu de l'article n°$i.\nNous sommes sur quelque chose de qualité!")
                    ->setImage("http://placehold.it/700x400")
                    ->setAmount(10)
                    ->setPrice(10);
            $manager->persist($article);
        }

        $manager->flush();
    }
}
