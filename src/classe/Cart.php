<?php

namespace App\classe;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\Persistence\ManagerRegistry;

class Cart{

    public  $session;
    private $entityManager;

    public function __construct(RequestStack $requestStack, ManagerRegistry $doctrine){
            $this->session = $requestStack->getSession();
            $this->entityManager = $doctrine->getManager();
    }

    public function add($id){

        $cart = $this->session->get('cart', []); // Si la session est nulle, on renvoie un tableau vide

        if(!empty($cart[$id])){
            $cart[$id]++;
        }else{
            $cart[$id] = 1;
        }

        $this->session->set('cart', $cart);
    }

    public function get(){
        return $this->session->get('cart');
    }

    public function remove(){
        return $this->session->remove('cart');
    }

    public function delete($id){
        $cart = $this->session->get('cart', []);
        unset($cart[$id]);
        return $this->session->set('cart', $cart);
    }

    public function decrease($id){
        // Vérifier su la quatité du produit est = 1
        $cart = $this->session->get('cart', []);

        if($cart[$id] > 1){
            $cart[$id]--;
        }else{
            unset($cart[$id]);
        }
        return $this->session->set('cart', $cart);
    }

    public function getFull(){

        $cartComplete = [];

        if($this->get()){
            foreach($cart->get() as $id => $quantity){

                $produit_object = $this->entityManager->getRepository(Product::class)->findOneById($id);

                if($produit_object){
                    $this->delete($id);
                    continue;
                }
                
                $cartComplete[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                ];                    
                
            }            
        }

        return $cartComplete;
    }

}