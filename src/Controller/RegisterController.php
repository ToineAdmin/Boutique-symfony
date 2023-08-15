<?php

namespace App\Controller;

use App\Entity\User;
use App\Classes\Mail;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{

    private $entityManager; // correspond à doctrine pour l'initialisé dans le constructeur car sera utilisé dans plusieurs fonctions

    public function __construct(EntityManagerInterface $entityManager) // entityManagerInterface = injection de dépendance
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/inscription', name: 'register')]
    public function index(Request $request, UserPasswordHasherInterface $encoder): Response
    {
        $notification = null;

        // Fonction permettant de créer le formulaire
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        // d'écouter le submit
        $form->handleRequest($request);
        // vérifier les données
        if($form->isSubmitted() && $form->isValid()){

            $user = $form->getData(); // récupérer les données (utiliser dd($user)pour vérifier)

            $search_email = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());

            if(!$search_email){ // si l'utilisateur n'existe pas

                $password = $encoder->hashPassword($user, $user->getPassword()); // crypte le password
                $user->setPassword($password); // le réinjecte dans l'objet user
                $this->entityManager->persist($user); 
                $this->entityManager->flush();

                $mail = new Mail();
                $content ="Bonjour" . $user->getFirsname() . "<br/>Bienvenue sur la première Boutique consacré au 100% made in France <br/> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin rutrum iaculis enim, eu congue lorem maximus sit amet. Sed posuere at massa et pretium. ";
                $mail->send($user->getEmail(), $user->getFirstname(), 'Bienvenue sur la Boutique', $content);

                $notification = 'Votre inscription a été prise en compte';


            }else{
                $notification = 'L\'email que vous avez renseigné existe déjà';
            }




        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
}
