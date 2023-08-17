<?php

namespace App\Controller;


use App\Classes\Cart;
use App\Entity\Order;
use App\Form\OrderType;
use App\Entity\OrderDetails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('/commande', name: 'order')]
    public function index(Cart $cart, Request $request): Response
    {
        if (!$this->getUser()->getAdresses()->getValues()) { //recupère les adresses , si vide =>
            return $this->redirectToRoute('account_adress_add');
        }

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser() // pour pouvoir récupérer le user dans le formulaire pour n'afficher que les adresses de ce user
        ]);


        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart->getFull()
        ]);
    }
    #[Route('/commande/recap', name: 'order_recap', methods: ["POST"])]

    public function add(Cart $cart, Request $request): Response
    {

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser() // pour pouvoir récupérer le user dans le formulaire pour n'afficher que les adresses de ce user
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //enregistrer commande
            $date = new \DateTimeImmutable(); //récup la date
            $carriers = $form->get('carriers')->getData(); // recup les données du transporteur
            $delivery = $form->get('adresses')->getData(); // recup l'adress de livraison

            //faire de l'adresse une grande chaine de caractère
            $delivery_content = $delivery->getFirstname() . ' ' . $delivery->getLastname();
            $delivery_content .= '<br/>' . $delivery->getPhone();

            if ($delivery->getCompany()) {

                $delivery_content .= '<br/>' . $delivery->getCompany();
            }

            $delivery_content .= '<br/>' . $delivery->getAdress();
            $delivery_content .= '<br/>' . $delivery->getPostal() . ' ' . $delivery->getCity();
            $delivery_content .= '<br/>' . $delivery->getCountry();
            $delivery_content .= '<br/>' . $delivery->getCompany();
            
            
            // Enregistre la commande

            $order = new Order();
            $uniqueId = bin2hex(random_bytes(8)); // évite l'ajout de UTC et du timestamp dans l'id unique
            $reference = $date->format('dmY') . '-' . $uniqueId;  // créé une référence pour la commande
            $order->setReference($reference);
            $order->setUser($this->getUser());
            $order->setCreatedAt($date);
            $order->setCarrierName($carriers->getName());
            $order->setCarrierPrice($carriers->getPrice());
            $order->setDelivery($delivery_content);
            $order->setState(0);

            $this->entityManager->persist($order);

            //enregistrer mes produits


            foreach ($cart->getFull() as $product) {
                $orderDetails = new OrderDetails();
                $orderDetails->setMyOrder($order);
                $orderDetails->setProduct($product['product']->getName());
                $orderDetails->setQuantity($product['quantity']);
                $orderDetails->setPrice($product['product']->getPrice());
                $orderDetails->setTotal($product['product']->getPrice() * $product['quantity']);
                $this->entityManager->persist($orderDetails);
            }



            $this->entityManager->flush();
     

            // mettre dans le if le return pour éviter l'accès à cette page sans passer par le panier etc => sinon erreurs 
            return $this->render('order/add.html.twig', [
                'cart' => $cart->getFull(),
                'carriers' => $carriers, //renvoie à twig pour récupérer le prix de la livraison
                'delivery' => $delivery_content,
                'reference' => $order->getReference()
            ]);
        }

        return $this->redirectToRoute('cart');
    }
}
