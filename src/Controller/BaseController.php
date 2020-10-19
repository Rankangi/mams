<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
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

        $repo = $this->getDoctrine()->getRepository(Category::class);

        $categories = $repo->findAll();

        return $this->render('base/index.html.twig', [
            'controller_name' => 'BaseController',
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/article/{id}", name="article_show")
     * @param Article $article
     * @param ArticleRepository $repo
     * @return Response
     */
    public function showArticle(Article $article, ArticleRepository $repo)
    {

        return $this->render('base/article.html.twig', [
            'article' => $article
        ]);
    }

    /**
     * @Route("/category/{id}", name="category_show")
     * @param Category $category
     * @param CategoryRepository $repo
     * @return Response
     */
    public function showCategory(Category $category, CategoryRepository $repo)
    {

        $categories = $repo->findAll();

        return $this->render('base/category.html.twig', [
            'categories' => $categories,
            'category' => $category
        ]);
    }


}
