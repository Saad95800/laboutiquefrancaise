<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\classe\Cart;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\Product;

class StripeController extends AbstractController
{

    #[Route('/commande/create-session/{reference}', name: 'app_stripe_create_session')]
    public function index(ManagerRegistry $doctrine, Cart $cart, $reference): JsonResponse
    {
        $products_for_stripe = [];
        $YOUR_DOMAIN = 'http://127.0.0.1:8000/';

        $order = $doctrine->getmanager()->getRepository(Order::class)->findOneByReference($reference);
        // dd($order->getOrderDetails());
        if(!$order){
            return $this->json(['error' => 'order']);
        }
        foreach($order->getOrderDetails()->getValues() as $product){

            $product_object = $doctrine->getmanager()->getRepository(Product::class)->findOneByName($product->getProduct());
            $products_for_stripe[] = [
                # Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
                'quantity' => $product->getQuantity(),
                'currency' => 'eur',
                'amount' => $product->getPrice(),
                'name' => $product->getProduct(),
                'images' => [$YOUR_DOMAIN.'/uploads/'.$product_object->getIllustration()]
            ];
        }

        $products_for_stripe[] = [
            # Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
            'quantity' => 1,
            'currency' => 'eur',
            'amount' => $order->getCarrierPrice() * 100,
            'name' => $order->getCarrierName(),
            'images' => [$YOUR_DOMAIN]
        ];

        // dd($products_for_stripe);
        Stripe::setApiKey('sk_test_51L491BLdUiTcQQFNQG7qpXc6ldjcjaHMQ4kaGPhSWzW7rMStlDGUDXG64wqDrDzkGGPh29nJ0JnjMkgisDewemz800Wk1lPuPI');
           
        $checkout_session = Session::create([
        'customer_email' => $this->getUser()->getEmail(),
        'line_items' => [$products_for_stripe],
        'mode' => 'payment',
        'success_url' => $YOUR_DOMAIN . 'commande/merci/{CHECKOUT_SESSION_ID}',
        'cancel_url' => $YOUR_DOMAIN . 'commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setStripeSessionId($checkout_session->id);

        $doctrine->getmanager()->flush($order);

        return $this->json(['id' => $checkout_session->id]);
    }
}
