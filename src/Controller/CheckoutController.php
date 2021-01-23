<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Article;
use App\Entity\Commande;
use App\Entity\PrepareCommande;
use App\Entity\User;
use App\Form\AdresseType;
use DateTime;
use Exception;
use Konekt\PdfInvoice\InvoicePrinter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


/**
 * @Route("/checkout", name="checkout")
 */
class CheckoutController extends AbstractController
{
    /**
     * @Route("/aled/{sessionId}", name="")
     * @param string $sessionId
     * @param Request $request
     * @return Response
     * @throws ApiErrorException
     * @IsGranted("ROLE_USER")
     */
    public function index(string $sessionId, Request $request)
    {
        $commande = $this->getDoctrine()->getRepository(PrepareCommande::class)->findOneBy(['sessionId' => $sessionId]);



        Stripe::setApiKey($this->getParameter('stripe_secret_key'));
        $checkout_session = Session::create([
            'allow_promotion_codes' => true,
            'payment_method_types' => ['card'],
            'customer' => $this->getUser()->getCustomerID(),
            'metadata' => [
                'sessionId' => $sessionId,
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
     * @Route("/success", name="_success")
     * @param Request $request
     * @param MailerInterface $mailer
     * @return Response
     * @throws ApiErrorException
     * @throws TransportExceptionInterface
     */
    public function checkout_success(Request $request, MailerInterface $mailer): Response
    {

        // Récupération des données
        Stripe::setApiKey($this->getParameter('stripe_secret_key'));
        $data = Session::retrieve($request->query->get('session_id'))->metadata->values();
        $prepcommande = $this->getDoctrine()->getRepository(PrepareCommande::class)->findOneBy(['sessionId' => $data[0]]);

        // Changement de Table : PrepareCommande -> Commande
        $commande = new Commande();
        $commande->setStatut("Payée");
        $commande->setUser($prepcommande->getUser());
        $commande->setArticle($prepcommande->getArticle());
        $commande->setShippingAddress($prepcommande->getShippingAddress());
        $commande->setBillingAddress($prepcommande->getBillingAddress());
        $commande->setAmount($prepcommande->getAmount());
        $commande->setSessionId($prepcommande->getSessionId());

        $commandes = $this->getDoctrine()->getRepository(Commande::class)->findBy(["Date" => new DateTime()]);
        $nb = count($commandes) + 1;
        // Mise à jour du nombre d'article restant
        $article = $prepcommande->getArticle();
        $article->setAmount($article->getAmount()-$prepcommande->getAmount());

        // Création de la facture
        $invoice = new InvoicePrinter("A4", "€", "fr");
        $invoice->setNumberFormat('.', ' ', 'right', 'true', 'false');

        /* Header settings */
        $invoice->setLogo("../public/uploads/images/article/468827-parrot-parrot_security-Linux-Debian-hacking.jpg");   //logo image path
        $invoice->setColor("#007fff");      // pdf color scheme
        $invoice->setType("Facture");    // Invoice Type
        $invoice->setReference("INV-".date('ymd').$nb);   // Reference
        $invoice->setDate(date('d/m/Y',time()));   //Billing Date
        $invoice->setTime(date('H:i:s',time()));   //Billing Time
//        $invoice->setDue(date('M dS ,Y',strtotime('+3 months')));    // Due Date
        $invoice->setFrom(array("JoyCreation (Josiane Bannwarth)","23 rue de blodelsheim","68740 Rumersheim-Le-Haut", "France", "SIRET: 0123456789"));
        $invoice->setTo(array($commande->getBillingAddress()->getFirstName() . " " . $commande->getBillingAddress()->getLastName() ,$commande->getUser()->getEmail(),$commande->getBillingAddress()->getStreet(),$commande->getBillingAddress()->getCodePostal() . " " . $commande->getBillingAddress()->getCity() . " " . $commande->getBillingAddress()->getCountry()));
        $invoice->addItem($commande->getArticle()->getTitle(),$commande->getArticle()->getDescription(),$commande->getAmount(),0,$commande->getArticle()->getPrice()/100,0,$commande->getAmount()*$commande->getArticle()->getPrice()/100);

        $invoice->addTotal("Total",$commande->getAmount()*$commande->getArticle()->getPrice()/100);
        $invoice->addTotal("VAT 0%",0);
        $invoice->addTotal("Total due",$commande->getAmount()*$commande->getArticle()->getPrice()/100,true);

        $invoice->addBadge("Facture Payée");

        $invoice->addTitle("Note importante");

        $invoice->addParagraph("TVA non applicable, art. 293 B du CGI");

        $invoice->setFooternote("JoyCreation");

        if (!file_exists('../factures/'.$commande->getBillingAddress()->getFirstName())) {
            mkdir('../factures/'.$commande->getBillingAddress()->getFirstName(), 0777, true);
        }

        $invoice->render("../factures/".$commande->getBillingAddress()->getFirstName()."/INV-".date('ymd').$nb.".pdf",'F');

        // Envoie de la facture
        $email = (new Email())
            ->from('no-reply@joycreation.fr')
            ->to($this->getUser()->getUsername())
            ->attachFromPath("../factures/".$commande->getBillingAddress()->getFirstName()."/INV-".date('ymd').$nb.".pdf", 'facture');

        $mailer->send($email);

        // Sauvegarde dans la BDD
        $commande->setDate(new DateTime());
        $commande->setFacture("INV-".date('ymd').$nb);
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($commande);
        $manager->remove($prepcommande);
        $manager->persist($article);
        $manager->flush();
        /* I => Display on browser, D => Force Download, F => local path save, S => return document as string */

        return $this->redirectToRoute('checkout_show_success');
    }

    /**
     * @Route("/success_show", name="_show_success")
     * @return Response
     */
    public function show_success(): Response
    {
        return $this->render('checkout/success.html.twig');
    }

    /**
     * @Route("/billing/{sessionId}", name="_getBillingAdresse")
     * @param Request $request
     * @param string $sessionId
     * @return Response
     */
    public function getBillingAdresse(Request $request, string $sessionId): Response
    {
        $billingAdresse = new Adresse();
        $street = $request->query->get('street');

        $billingForm = $this->createForm(AdresseType::class, $billingAdresse);
        $billingForm->handleRequest($request);

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $this->getUser()->getUsername()]);

        if ($billingForm->isSubmitted() && $billingForm->isValid()){
            $adresse = $billingForm->getData();
            $commande = $this->getDoctrine()->getRepository(PrepareCommande::class)->findOneBy(['sessionId' => $billingForm->get('sessionId')->getData()]);
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
     * @Route("/shipping", name="_getShippingAdresse")
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

        if (!$user->isVerified()){
            $this->addFlash('alerte', "Veuillez d'abord confirmer votre email !");
            return $this->redirectToRoute('article_show', ["id" => $id]);
        }

        if ($shippingForm->isSubmitted() && $shippingForm->isValid()){
            $adresse = $shippingForm->getData();
            $adresse->setUser($user);
            $shippingAdresse = $this->getDoctrine()->getRepository(Adresse::class)->findByAdresse($adresse);
            if ($shippingAdresse == null){
                $shippingAdresse = $adresse;
                $manager->persist($shippingAdresse);
            }
            $commande = $this->getDoctrine()->getRepository(PrepareCommande::class)->findOneBy(['sessionId' => $shippingForm->get('sessionId')->getData()]);
            $commande->setShippingAddress($shippingAdresse);

            if ($shippingForm->get('differentBillingAddress')->getData()){
                $manager->persist($commande);
                $manager->flush();

                return $this->redirectToRoute("checkout_getBillingAdresse", ["sessionId" => $commande->getSessionId()]);

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
                $commande = new PrepareCommande();
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
