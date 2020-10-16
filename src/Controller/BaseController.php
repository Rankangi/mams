<?php

namespace App\Controller;

use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends AbstractController
{
    /**
     * @Route("/", name="base")
     */
    public function index()
    {

        $repo = $this->getDoctrine()->getRepository(Article::class);

        $articles = $repo->findAll();

        return $this->render('base/index.html.twig', [
            'controller_name' => 'BaseController',
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/{id}", name="article_show")
     * @param Article $article
     * @return Response
     */
    public function showArticle(Article $article)
    {
        return $this->render('base/article.html.twig', [
            'article' => $article
        ]);
    }
}
