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
            'payment_method_types' => ['card'],
            'line_items' => [[
                'quantity' => $amount,
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $article->getPrice()*100,
                    'product_data' => [
                        'name' => $article->getTitle(),
                        'images' => [$article->getImage()],
                        'description' => $article->getDescription(),
                    ],
                ],
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('checkout_success',['id' => $article->getId(), 'amount' => $amount],UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => "https://example.com/cancel",
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
        $id = $request->query->get('id');
        $amount = $request->query->get('amount');
        return $this->render('checkout/success.html.twig');
    }



}
