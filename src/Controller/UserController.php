<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\User;
use App\Form\DefaultAdresseType;
use App\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

        return $this->render('base/user.html.twig', ['userForm' => $userForm->createView(), "shippingForm" => $shippingForm->createView(), 'listeCommandes' => $user->getCommandes()]);
    }

    /**
     * @param String $id
     * @return BinaryFileResponse
     * @Route("/user/download/{id}", name="download_invoice")
     */
    public function downloadInvoice(String $id): BinaryFileResponse
    {
        $commande = $this->getDoctrine()->getRepository(Commande::class)->findOneBy(["sessionId" => $id]);
        if ($commande == null){
            $this->addFlash("alerte", "SessionID invalide");
        }else{
            return $this->file("../factures/". $commande->getBillingAddress()->getFirstName()."/".$commande->getFacture().".pdf");
        }
    }
}
