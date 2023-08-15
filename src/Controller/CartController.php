<?php

namespace App\Controller;

use App\Classes\Cart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{

    #[Route('/mon-panier', name: 'cart')]
    public function index(Cart $cart): Response
    {

        return $this->render('cart/index.html.twig',[
            'cart' => $cart->getFull()
        ]);
    }


    #[Route('/cart/add/{id}', name: 'add_to_cart')]
    public function addToCart(Cart $cart, $id): Response
    {
        $cart->add($id);

        return $this->redirectToRoute('cart');
        
    }

    #[Route('/cart/remove', name: 'remove_my_cart')]
    public function removeCart(Cart $cart): Response
    {
        $cart->remove();

        return $this->redirectToRoute('products');
        
    }

    #[Route('/cart/delete/{id}', name: 'delete_my_product')]
    
    public function deleteProduct(Cart $cart, $id): Response
    {
        $cart->delete($id);

        return $this->redirectToRoute('cart');
        
    }

    #[Route('/cart/deleteOneProduct/{id}', name: 'delete_one_product')]
    
    public function deleteOneProduct(Cart $cart, $id): Response
    {
        $cart->deleteOneProduct($id);

        return $this->redirectToRoute('cart');
        
    }
}
