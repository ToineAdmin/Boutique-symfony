<?php

namespace App\Classes;


use App\Entity\Products;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart
{ 
    
    private $entityManager;  
    private $session;

    public function __construct(SessionInterface $session,EntityManagerInterface $entityManager)
    {
        $this-> session = $session;
        $this->entityManager = $entityManager;
    }


    public function add($id)
    {
        
        
        $cart = $this->session->get('cart', []);
        
        if(!empty($cart[$id])){  // si tu as déjà dans le panier un produit déjà inséré
            $cart[$id] ++;
        }else {
            $cart[$id] = 1;
        }


        $this->session->set('cart', $cart);

    }

    public function deleteOneProduct($id)
    {
        $cart = $this->session->get('cart', []);

        if( $cart[$id] > 1 ){
            $cart[$id] --;
        } else {
            unset($cart[$id]);
        }

        $this->session->set('cart', $cart);
    }

    public function get()
    {
        return $this->session->get('cart');
    }

    public function remove()
    {
        return $this->session->remove('cart');
    }

    public function delete($id)
    {
        $cart = $this->session->get('cart', []);

        unset($cart[$id]);

        return $this->session->set('cart', $cart);
    }

    public function getFull()
    {
        $cartData = [];
        
        if($this->get())
        {
            foreach ($this->get() as $id=>$quantity)
            {
                $product_object = $this->entityManager->getRepository(Products::class)->findOneById($id);

                if(!isset($product_object))
                {
                    $this->delete($id);
                    continue; // sort de la boucle foreach (sécurité si quelqu'un ajoute un id dans l'url)
                }
                $cartData[] = [
                    'product' => $product_object,
                    'quantity' => $quantity
                ];
            }
        }

        return $cartData;
    }
}