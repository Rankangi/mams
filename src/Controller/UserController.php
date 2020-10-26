<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Stripe\BillingPortal\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     * @IsGranted("ROLE_USER")
     */
    public function index()
    {

        Stripe::setApiKey($this->getParameter('stripe_secret_key'));
        $session = Session::create([
            'customer' => $this->getUser()->getCustomerID(),
            'return_url' => $this->generateUrl('base',[], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);


        return $this->redirect($session->url);
    }
}
