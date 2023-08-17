<?php

namespace App\Controller;

use App\Classes\Cart;
use App\Classes\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande/merci/{checkoutSessionId}', name: 'order_validate')]
    public function index($checkoutSessionId, Cart $cart): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByCheckoutSessionId($checkoutSessionId);

        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }


        if ($order->getState() == 0) {
            $cart->remove();
            $order->setState(1);
            $this->entityManager->flush();


            //envoyer mail au client
            $mail = new Mail();
            $content = "Bonjour" . $order->getUser()->getFirstname() . "<br/>Merci pour votre commande<br/> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin rutrum iaculis enim, eu congue lorem maximus sit amet. Sed posuere at massa et pretium. ";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre commande la Boutique est validée', $content);
        }

        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}
