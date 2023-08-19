<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use App\Classes\Mailjet;
use App\Entity\ResetPassword;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/mot-de-passe-oublie', name: 'reset_password')]
    public function index(Request $request): Response
    {
        if($this->getUser()){
            return $this->redirectToRoute('home');   
        }

        if($request->get('email')){
            $user = $this->em->getRepository(User::class)->findOneByEmail($request->get('email'));

            if($user){
                //enregistre en base la demande de reset

                $reset_password = new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt(new DateTimeImmutable());
                $this->em->persist($reset_password);
                $this->em->flush();

                //Envoie email avec lien de réinitialisation
                $url=$this->generateUrl('update_password', [
                    'token' => $reset_password->getToken()
                ]);

                $content ="Bonjour". ' ' . $user->getFirstname(). ",<br/> Vous avez demandé à réinitialiser votre mot de passe la Boutique. <br/>";
                $content .= "Merci de bien vouloir cliquer sur le lien suivant : <a href='.$url.'>mettre à jour votre mot de passe</a> ";

                $mail = new Mailjet();
                $mail->send($user->getEmail(), $user->getFirstname().' '. $user->getLastname(),'Réinitialiser votre mot de passe La Boutique', $content);
                
                $this->addFlash('notice', 'Vous allez recevoir dans quelques seconde un email afin de réinitialiser votre mot de passe');
            }else {
                $this->addFlash('notice', 'Cette adresse email est inconnue');
            }
        }

        return $this->render('reset_password/index.html.twig');
    }

    #[Route('/modifer-mot-de-passe/{token}', name: 'update_password')]
    public function update($token, Request $request, UserPasswordHasherInterface $encoder): Response
    {
        $reset_password = $this->em->getRepository(ResetPassword::class)->findOneByToken($token);

        if(!$reset_password){
            $this->redirectToRoute('reset_password');
        }

        //verifier si le createdAt = now - 1h

        $now = new DateTimeImmutable();
        if($now > $reset_password->getCreatedAt()->modify('+ 1 hour')){
            
            $this->addFlash('notice', 'Votre de demande de modification de mot de passe a expiré. Merci de la renouveler.');
            return $this->redirectToRoute('reset_password');
        }
        
        //vue avec modifier mot de passe.
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        //recupère password encode et flush
        if($form->isSubmitted() && $form->isValid()){
            $new_pwd = $form->get('new_password')->getData();
            $password = $encoder->hashPassword($reset_password->getUser(), $new_pwd);

            $reset_password->getUser()->setPassword($password);
            $this->em->flush();

            $this->addFlash('notice', 'Votre mot de passe a bien été mis à jour');

            return $this->redirectToRoute('app_login');

        }
        return $this->render('reset_password/update.html.twig',[
            'form' => $form->createView()
        ]);
    }
}
