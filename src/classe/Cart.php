<?php

namespace App\classe;
use Symfony\Component\HttpFoundation\RequestStack;

class Cart{

    public  $session;

    public function __construct(RequestStack $requestStack){
            $this->session = $requestStack->getSession();
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

}