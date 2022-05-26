<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Address;
use App\Form\AddressType;
use Doctrine\Persistence\ManagerRegistry;

class AccountAddressController extends AbstractController
{

    private $entityManager;

    public function __construct(ManagerRegistry $doctrine){
        $this->entityManager = $doctrine->getManager();
    }

    #[Route('/compte/adresses', name: 'app_account_address')]
    public function index(): Response
    {
        return $this->render('account/address.html.twig',);
    }

    #[Route('/compte/ajouter-une-adresse', name: 'app_account_address_add')]
    public function add(Request $request)
    {
        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $address->setUser($this->getUser());
            $this->entityManager->persist($address);
            $this->entityManager->flush($address);
            return $this->redirectToRoute('app_account_address');
        }

        return $this->render('account/address_form.html.twig',[
            'form' => $form->createView()
        ]);
    }

    #[Route('/compte/modifier-une-adresse/{id}', name: 'app_account_address_edit')]
    public function edit(Request $request, $id)
    {
        $address = $this->entityManager->getRepository(Address::class)->findOneById($id);

        if(!$address || $address->getUser() != $this->getUser()){ // SI l'adresse n'éxiste pas en BDD ou si L'user qui modifie l'adresse est différent de l'user à qui appartient l'adresse
            return $this->redirectToRoute('account_address');
        }
        $form = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->entityManager->flush($address);
            return $this->redirectToRoute('app_account_address');
        }

        return $this->render('account/address_form.html.twig',[
            'form' => $form->createView()
        ]);
    }

    #[Route('/compte/supprimer-une-adresse/{id}', name: 'app_account_address_delete')]
    public function delete($id)
    {
        $address = $this->entityManager->getRepository(Address::class)->findOneById($id);

        if($address || $address->getUser() == $this->getUser()){ // Si l'adresse éxiste en BDD et si L'user qui modifie l'adresse est bien l'user à qui appartient l'adresse
            $this->entityManager->remove($address);
            $this->entityManager->flush($address);
        }

        
        return $this->redirectToRoute('app_account_address');

    }

}
