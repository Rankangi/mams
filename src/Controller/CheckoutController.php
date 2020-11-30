<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Article;
use App\Entity\Commande;
use App\Entity\User;
use App\Form\AdresseType;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
     * @Route("/aled/{sessionId}", name="checkout")
     * @param string $sessionId
     * @param Request $request
     * @return Response
     * @throws ApiErrorException
     * @IsGranted("ROLE_USER")
     */
    public function index(string $sessionId, Request $request)
    {
        $commande = $this->getDoctrine()->getRepository(Commande::class)->findOneBy(['sessionId' => $sessionId]);



        Stripe::setApiKey($this->getParameter('stripe_secret_key'));
        $checkout_session = Session::create([
            'allow_promotion_codes' => true,
            'payment_method_types' => ['card'],
            'customer' => $this->getUser()->getCustomerID(),
            'metadata' => [
                'quantity' => $commande->getAmount(),
                'id' => $commande->getArticle()->getId(),
            ],
            'line_items' => [[
                'quantity' => $commande->getAmount(),
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $commande->getArticle()->getPrice(),
                    'product_data' => [
                        'name' => $commande->getArticle()->getTitle(),
                        'images' => [$commande->getArticle()->getImage()],
                        'description' => $commande->getArticle()->getDescription(),
                    ],
                ],
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('checkout_success',[], UrlGeneratorInterface::ABSOLUTE_URL) . "?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url' => $this->generateUrl('article_show',['id' => $commande->getArticle()->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
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

    /**
     * @Route("/checkout/billing/{sessionId}", name="getBillingAdresse")
     * @param Request $request
     * @param string $sessionId
     * @return Response
     */
    public function getBillingAdresse(Request $request, string $sessionId){
        $billingAdresse = new Adresse();
        $street = $request->query->get('street');

        $billingForm = $this->createForm(AdresseType::class, $billingAdresse);
        $billingForm->handleRequest($request);

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $this->getUser()->getUsername()]);

        if ($billingForm->isSubmitted() && $billingForm->isValid()){
            $adresse = $billingForm->getData();
            $commande = $this->getDoctrine()->getRepository(Commande::class)->findOneBy(['sessionId' => $billingForm->get('sessionId')->getData()]);
            $adresse->setUser($user);
            $billingAdresse = $this->getDoctrine()->getRepository(Adresse::class)->findByAdresse($adresse);
            if ($billingAdresse == null){
                $billingAdresse = $adresse;
                $this->getDoctrine()->getManager()->persist($billingAdresse);
            }
            $commande->setBillingAddress($billingAdresse);
            $this->getDoctrine()->getManager()->persist($commande);
            $this->getDoctrine()->getManager()->flush();
            $shippingForm = $this->createForm(AdresseType::class, $commande->getShippingAddress());
            return $this->render("checkout/confirm.html.twig", ['shippingForm' => $shippingForm->createView(), 'billingForm' => $billingForm->createView(), 'commande' => $commande]);
        }
        else if($street != null){
            foreach ($user->getAdresses() as $adresse){
                if ($adresse->getStreet() == $street){
                    $billingForm->get('street')->setData($adresse->getStreet());
                    $billingForm->get('city')->setData($adresse->getCity());
                    $billingForm->get('codePostal')->setData($adresse->getCodePostal());
                    $billingForm->get('country')->setData($adresse->getCountry());
                    $billingForm->get('firstName')->setData($adresse->getFirstName());
                    $billingForm->get('lastName')->setData($adresse->getLastName());
                }
            }
        }
        else if ($user->getDefaultAddress() != null){
            $userAdresse = $user->getDefaultAddress();
            $billingForm->get('street')->setData($userAdresse->getStreet());
            $billingForm->get('city')->setData($userAdresse->getCity());
            $billingForm->get('codePostal')->setData($userAdresse->getCodePostal());
            $billingForm->get('country')->setData($userAdresse->getCountry());
            $billingForm->get('firstName')->setData($userAdresse->getFirstName());
            $billingForm->get('lastName')->setData($userAdresse->getLastName());
        }
        $billingForm->get('sessionId')->setData($sessionId);

        return $this->render('checkout/shipping.html.twig', ['form' => $billingForm->createView(), "shipping" => false]);
    }

    /**
     * @Route("/checkout/shipping", name="getShippingAdresse")
     * @param Request $request
     * @return Response
     * @throws Exception
     * @IsGranted("ROLE_USER")
     */
    public function getShippingAdresse(Request $request){

        $shippingAdresse = new Adresse();
        $amount = $request->query->get('amount');
        $id = $request->query->get('id');
        $street = $request->query->get('street');

        $shippingForm = $this->createForm(AdresseType::class, $shippingAdresse);

        $shippingForm->handleRequest($request);
        $manager = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $this->getUser()->getUsername()]);

        if ($shippingForm->isSubmitted() && $shippingForm->isValid()){
            $adresse = $shippingForm->getData();
            $adresse->setUser($user);
            $shippingAdresse = $this->getDoctrine()->getRepository(Adresse::class)->findByAdresse($adresse);
            if ($shippingAdresse == null){
                $shippingAdresse = $adresse;
                $manager->persist($shippingAdresse);
            }
            $commande = $this->getDoctrine()->getRepository(Commande::class)->findOneBy(['sessionId' => $shippingForm->get('sessionId')->getData()]);
            $commande->setShippingAddress($shippingAdresse);

            if ($shippingForm->get('differentBillingAddress')->getData()){
                $manager->persist($commande);
                $manager->flush();

                return $this->redirectToRoute("getBillingAdresse", ["sessionId" => $commande->getSessionId()]);

            }else {
                $commande->setBillingAddress($shippingAdresse);
                $manager->persist($commande);
                $manager->flush();
                return $this->render('checkout/confirm.html.twig', ['shippingForm' => $shippingForm->createView(), 'billingForm' => $shippingForm->createView(), 'commande' => $commande]);
            }
        } else {
            if ($street != null){
                foreach ($user->getAdresses() as $adresse){
                    if ($adresse->getStreet() == $street){
                        $sessionId = $request->query->get('sessionId');
                        $shippingForm->get('street')->setData($adresse->getStreet());
                        $shippingForm->get('city')->setData($adresse->getCity());
                        $shippingForm->get('codePostal')->setData($adresse->getCodePostal());
                        $shippingForm->get('country')->setData($adresse->getCountry());
                        $shippingForm->get('firstName')->setData($adresse->getFirstName());
                        $shippingForm->get('lastName')->setData($adresse->getLastName());
                        $shippingForm->get('sessionId')->setData($sessionId);
                    }
                }

            }
            else if ($shippingForm->get('sessionId')->getData() == null){
                if ($user->getDefaultAddress() != null){
                    $userAdresse = $user->getDefaultAddress();
                    $shippingForm->get('street')->setData($userAdresse->getStreet());
                    $shippingForm->get('city')->setData($userAdresse->getCity());
                    $shippingForm->get('codePostal')->setData($userAdresse->getCodePostal());
                    $shippingForm->get('country')->setData($userAdresse->getCountry());
                    $shippingForm->get('firstName')->setData($userAdresse->getFirstName());
                    $shippingForm->get('lastName')->setData($userAdresse->getLastName());
                }
                $article = $this->getDoctrine()->getRepository(Article::class)->find($id);
                $commande = new Commande();
                $commande->setAmount($amount);
                $commande->setArticle($article);
                $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $this->getUser()->getUsername()]);
                $commande->setUser($user);
                $commande->setSessionId(bin2hex(random_bytes(32)));
                $commande->setStatut("checkout");
                $shippingForm->get('sessionId')->setData($commande->getSessionId());
                $manager->persist($commande);
                $manager->flush();
            }
            return $this->render('checkout/shipping.html.twig', ['form' => $shippingForm->createView(), "shipping" => true]);
        }
    }



}
