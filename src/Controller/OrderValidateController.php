<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Order;

class OrderValidateController extends AbstractController
{

    private $entityManager;

    public function __construct(ManagerRegistry $doctrine){
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/commande/merci/{stripeSessionId}', name: 'app_order_validate')]
    public function index($stripeSessionId): Response
    {

        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if(!$order || $order->getUser() != $this->getUser()){
            return $this->redirectToRoute('app_home');
        }

        // Modifier le statut isPaid de notre commande à 1

        if(!$order->isIsPaid()){
            $order->setIsPaid(1);
            $this->entityManager->flush($order);
        }

        // Envoyer un email à notre client pour lui confirmer sa commande

        // Afficher les quelques informations de la commande de l'utilisateur


        return $this->render('order_validate/index.html.twig', [
            'order' => $order
        ]);
    }
}
