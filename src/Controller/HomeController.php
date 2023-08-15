<?php

namespace App\Controller;

use App\Classes\Mail;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(SessionInterface $session): Response
    {    
        return $this->render('home/index.html.twig');
    }
}
