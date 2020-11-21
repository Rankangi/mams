<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Stripe\BillingPortal\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param ObjectManager $manager
     * @return Response
     */
    public function index(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $user = $manager->getRepository(User::class)->find($this->getUser()->getId());

        if ($request->request->count() > 0){
            if($user->getDefaultAddress() == null){
                $user->setDefaultAddress(new Adresse());
            }
            $user->setFirstName($request->request->get('Nom'))
                 ->setlastName($request->request->get('Prénom'))
                 ->setNumber($request->request->get('Number'));
            $user->getDefaultAddress()->setStreet($request->request->get('Adresse'))
                 ->setCity($request->request->get('Ville'))
                 ->setCountry($request->request->get('Pays'))
                 ->setCodePostal($request->request->get('Cp'))
                 ->setFirst(true)
                 ->setUser($user);

            $manager->persist($user);
            $manager->flush();
        }


        return $this->render('base/user.html.twig', ['user' => $this->getUser()]);
    }
}
