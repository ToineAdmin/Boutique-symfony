<?php

namespace App\Controller;

use App\Classes\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderCancelController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager; 
    }

    #[Route('/commande/erreur/{checkoutSessionId}', name: 'order_cancel')]
    public function index($checkoutSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByCheckoutSessionId($checkoutSessionId);

        if(!$order || $order->getUser() != $this->getUser()){
            return $this->redirectToRoute('home');
        }

        // Envoyer email d'échec

        $mail = new Mail();
        $content = "Bonjour" . $order->getUser()->getFirsname() . "<br/>Oups il y a eu un problème avec votre commande<br/> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin rutrum iaculis enim, eu congue lorem maximus sit amet. Sed posuere at massa et pretium. ";
        $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), "Votre commande n'a pas abouti", $content);
        
        return $this->render('order_cancel/index.html.twig',[
            'order' => $order
        ]);
    }
}
