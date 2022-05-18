<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ChangePasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;

class AccountPasswordController extends AbstractController
{

    private $entityManager;

    public function __construct(ManagerRegistry $doctrine){
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/compte/modifier-mon-mot-de-passe', name: 'app_account_password')]
    public function index(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {

        $notification = null;
        $type_notif = "";

        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){ // SI le formulaire a été soumis e
            // Nous allons comparer le mot de passe saisi par le mot de passe en base

            $old_pwd = $form->get('old_password')->getData();

            if($passwordHasher->isPasswordValid($user, $old_pwd)){
                $new_pwd = $form->get('new_password')->getData();

                // encodage du nouveau mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $new_pwd);
                $user->setPassword($hashedPassword);
                // Pas besoin d'utiliser le methode persist puisque l'objet user est déjà géré par doctrine
                $this->entityManager->flush($user);
                $notification = "Votre mot de passe a bien été mis à jour.";
                $type_notif = "alert-info";
            }else{
                $notification = "Votre mot de passe actuel saisi n'est pas correct.";
                $type_notif = "alert-danger";
            }

        }


        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification,
            'type_notif' => $type_notif
        ]);
    }
}
