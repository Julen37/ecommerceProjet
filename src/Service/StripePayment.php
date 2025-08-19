<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripePayment
{
    public $redirectUrl;

    public function __construct()
    {
        Stripe::setApikey($_SERVER['STRIPE_SECRET_KEY']);
        Stripe::setApiVersion('2025-07-30.basil');
    }

    public function startPayment($cart, $shippingCost){
        // dd($cart);

        $cartProducts = $cart['cart']; // recuperation des produits du panier
        $products = [
            [
                'qte'=> 1,
                'price'=> $shippingCost,
                'name'=> "Delivery fees"
            ]

        ]; 

        foreach ($cartProducts as $value){ //boucle pour parcourir chaque produit du panier
            $productItem = []; // initialise un tableau vide pour stocker les infos du produit
            $productItem['name'] = $value['product']->getName(); //recupere le nom du produit
            $productItem['price'] = $value['product']->getPrice(); // recup le prix
            $productItem['qte'] = $value['quantity']; //recup la quantité
            $products[] = $productItem; // ajout du produit formaté au tableau des produits
        }

        $session = Session::create([ // creation de la session stripe
            'line_items'=>[ //produit qui vont etre payé
                array_map(fn(array $product) => [
                    'quantity'=> $product['qte'],
                    'price_data'=> [
                        'currency'=> 'Eur',
                        'product_data'=>[
                            'name'=> $product['name']
                        ],
                        'unit_amount'=> $product['price']*100, //rpix donnée en centimes donc on multiplie
                    ]
                ], $products)
            ],
            'mode'=>'payment', //mode de paiement
            'cancel_url'=> 'http://localhost:8000/pay/cancel', //si paiement annulé on redirige ici
            'success_url' => 'http://localhost:8000/pay/success', //si paiement réussi
            'billing_address_collection' => 'required', //si on autorise les factures
            'shipping_address_collection' => [ //pays ou on souhaite autoriser le paiement
                'allowed_countries' => ['FR','EG'],
            ],   
            'metadata'=> [
                // 'order_id'=>$cart->id, //id de la commande
            ]
        ]);

        $this->redirectUrl = $session->url; // redirection vers stripe pour le paiement
    }
    public function getStripeRedirectUrl(){ //permet de recuperer l'url de l'utilisateur pour stripe
        return $this->redirectUrl;
    }
}
