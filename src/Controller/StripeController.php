<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\classe\Cart;
use Symfony\Component\HttpFoundation\JsonResponse;

class StripeController extends AbstractController
{
    #[Route('/commande/create-session', name: 'app_stripe_create_session')]
    public function index(Cart $cart): JsonResponse
    {
        $products_for_stripe = [];
        $YOUR_DOMAIN = 'http://127.0.0.1:8000/';

        foreach($cart->getFull() as $product){


            $products_for_stripe[] = [
                # Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
                'quantity' => $product['quantity'],
                'currency' => 'eur',
                'amount' => $product['product']->getPrice(),
                'name' => $product['product']->getName(),
                'images' => [$YOUR_DOMAIN.'/uploads/'.$product['product']->getIllustration()]
            ];
        }

        Stripe::setApiKey('clé privée stripe');
           
        $checkout_session = Session::create([
        'line_items' => [$products_for_stripe],
        'mode' => 'payment',
        'success_url' => $YOUR_DOMAIN . '/success.html',
        'cancel_url' => $YOUR_DOMAIN . '/cancel.html',
        ]);

        return $this->json(['id' => $checkout_session->id]);
    }
}
