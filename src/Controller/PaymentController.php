<?php

 
namespace App\Controller;
 
use Stripe\Stripe;
use App\Classes\Cart;
use App\Entity\Order;
use App\Entity\Products;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 
class PaymentController extends AbstractController
{

    #[Route('/commande/create-session/{reference}', name: 'stripe_create_session')]
    public function index(EntityManagerInterface $entityManager, Cart $cart, $reference)
    {
        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);
        
        if(!$order){
            return $this->redirectToRoute('order');
        }



        $product_for_stripe = [];
        $YOUR_DOMAIN = 'http://127.0.0.1:8000';
 
        foreach ($order->getOrderDetails()->getValues() as $product) {
            $product_object = $entityManager->getRepository(Products::class)->findOneByName($product->getProduct());
            $product_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product->getPrice(),
                    'product_data' => [
                        'name' => $product->getProduct(),
                        'images' => [$YOUR_DOMAIN."/uploads/".$product_object->getIllustration()],
                    ],
                ],
                'quantity' => $product->getQuantity(),
            ];

        }

        $product_for_stripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $order->getCarrierPrice(),
                'product_data' => [
                    'name' => $order->getCarrierName(),
                    'images' => [$YOUR_DOMAIN],
                ],
            ],
            'quantity' => 1,
        ];



 
        Stripe::setApiKey('sk_test_51NeuBPLOmvQhST5CoieXSL1wOMSIbyV3qEruTP9INJcToo5HOi0RyI0udQnbyw6vOSDaR9cHqBd3cvYXJnPGjoYm00bsclTYRm');
 
        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'line_items' => [
                $product_for_stripe
            ],
            'payment_method_types' => [
                'card',
            ],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setCheckoutSessionId($checkout_session->id);
        $entityManager->flush();
 
        return $this->redirect($checkout_session->url);
    }
}