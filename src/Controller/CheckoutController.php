<?php

namespace App\Controller;

use App\Entity\Article;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CheckoutController extends AbstractController
{
    /**
     * @Route("/checkout/{id}", name="checkout")
     * @param Article $article
     * @return Response
     * @throws ApiErrorException
     */
    public function index(Article $article)
    {
        Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => 2000,
                    'product_data' => [
                        'name' => "test",
                        'images' => ["http://placehold.it/700x400"],
                    ],
                ],
                'quantity' => 10,
            ]],
            'mode' => 'payment',
            'success_url' => "https://example.com/success",
            'cancel_url' => "https://example.com/cancel",
        ]);
        $response = new Response();
        $response->headers->set('id', $checkout_session->id);
        return $response;
    }

}
