<?php

namespace App\Controller;

use App\Entity\Article;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CheckoutController extends AbstractController
{
    /**
     * @Route("/checkout/{id}", name="checkout")
     * @param Article $article
     * @param Request $request
     * @return Response
     * @throws ApiErrorException
     */
    public function index(Article $article, Request $request)
    {
        $amount = $request->query->get('amount');

        Stripe::setApiKey($this->getParameter('stripe_secret_key'));
        $checkout_session = Session::create([
            'allow_promotion_codes' => true,
            'payment_method_types' => ['card'],
            'metadata' => [
                'quantity' => $amount,
                'id' => $article->getId(),
            ],
            'line_items' => [[
                'quantity' => $amount,
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $article->getPrice(),
                    'product_data' => [
                        'name' => $article->getTitle(),
                        'images' => [$article->getImage()],
                        'description' => $article->getDescription(),
                    ],
                ],
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('checkout_success',[], UrlGeneratorInterface::ABSOLUTE_URL) . "?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url' => $this->generateUrl('base',[], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
        $response = new Response();
        $response->headers->set('id', $checkout_session->id);
        return $response;
    }

    /**
     * @Route("/success", name="checkout_success")
     * @param Request $request
     * @return Response
     */
    public function checkout_success(Request $request){

        Stripe::setApiKey($this->getParameter('stripe_secret_key'));
        $data = Session::retrieve($request->query->get('session_id'))->metadata->values();
        // On récupère la quantité vendue et l'id de l'article.
        $quantity = $data[1];
        $id = $data[0];

        $article = $this->getDoctrine()->getRepository(Article::class)->find($id);
        $article->setAmount($article->getAmount()-$quantity);
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($article);
        $manager->flush();

        return $this->redirectToRoute('show_success');
    }


    /**
     * @Route("/success_show", name="show_success")
     */
    public function show_success(){
        return $this->render('checkout/success.html.twig');
    }



}
