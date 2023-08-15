<?php

namespace App\Controller;
use App\Classes\Cart;
use App\Entity\Adress;
use App\Form\AddressType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AcountAdressController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/compte/adresses', name: 'account_adress')]
    public function index(): Response
    {
        return $this->render('account/adress.html.twig');
    }

    #[Route('/compte/ajouter-adresse', name: 'account_adress_add')]
    public function addAdress(Cart $cart, Request $request): Response
    {
        $adress = new Adress();

        $form = $this->createForm(AddressType::class, $adress);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $adress->setUser($this->getUser());
            $this->entityManager->persist($adress);
            $this->entityManager->flush();
            if($cart->get()){ // si il y a quelque chose dans mon panier redirige vers order
                return $this->redirectToRoute('order'); 
            }else
            return $this->redirectToRoute('account_adress');
        }

        return $this->render('account/adress_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/compte/modifier-adresse/{id}', name: 'account_adress_update')]
    public function updateAdress(Request $request, $id): Response
    {
        $adress = $this->entityManager->getRepository(Adress::class)->findOneById($id);

        if(!$adress || $adress->getUser() != $this->getUser()){
            return $this->redirectToRoute('account_adress');
        }

        $form = $this->createForm(AddressType::class, $adress);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->entityManager->flush();
            return $this->redirectToRoute('account_adress');
        }

        return $this->render('account/adress_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/compte/supprimer-adresse/{id}', name: 'account_adress_delete')]
    public function deleteAdress($id): Response
    {
        $adress = $this->entityManager->getRepository(Adress::class)->findOneById($id);

        if($adress && $adress->getUser() == $this->getUser()){
            $this->entityManager->remove($adress);
            $this->entityManager->flush();
        }


        return $this->redirectToRoute('account_adress');
    }
}