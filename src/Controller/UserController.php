<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\User;
use App\Form\AdresseType;
use App\Form\DefaultAdresseType;
use App\Form\UserType;
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
     * @return Response
     */
    public function index(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $user = $manager->getRepository(User::class)->find($this->getUser()->getId());
        $userForm = $this->createForm(UserType::class,$user);
        $userForm->handleRequest($request);

        $shippingAddress = $user->getDefaultAddress();
        $shippingForm = $this->createForm(DefaultAdresseType::class, $shippingAddress);
        $shippingForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()){
            $user = $userForm->getData();
            if ($user->getDefaultAddress() != null){
                $shippingAddress->setFirstName($user->getFirstName());
                $shippingAddress->setLastName($user->getLastName());
            }
            $manager->flush();
        }

        else if ($shippingForm->isSubmitted() && $shippingForm->isValid()){
            if ($shippingAddress == null){
                $shippingAddress = $shippingForm->getData();
                $shippingAddress->setFirstName($user->getFirstName());
                $shippingAddress->setLastName($user->getLastName());
                $shippingAddress->setUser($user);
                $user->setDefaultAddress($shippingAddress);
                $manager->persist($shippingAddress);
            }else{
                $shippingAddress = $shippingForm->getData();
            }
            $manager->flush();
        }

        return $this->render('base/user.html.twig', ['userForm' => $userForm->createView(), "shippingForm" => $shippingForm->createView()]);
    }
}
