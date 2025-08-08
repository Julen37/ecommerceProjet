<?php

namespace App\Service;

use App\Repository\ProductRepository;

class Cart{

    public function __construct(private readonly ProductRepository $productRepo){
    }
    
    public function getCart($session):array{
        // recupere les données du panier en session, ou tableau vide si il n'y a plus rien
        $cart = $session->get('cart',[]);
        // initialisation d'un tableau pour stocker les données du panier avec les infos de produit
        $cartWithData = [];
        //boucle sur les elements du panier pour recup les info de produit
        foreach ($cart as $id => $quantity) {
            // recupere le produit correspondant a l'id et quantite
            $cartWithData[] =[
                'product' => $this->productRepo->find($id), //recupere le produit via son id
                'quantity' => $quantity // quantité du produit dans le panier
            ];
        }
        //Calcul total du panier
        $total = array_sum(array_map(function ($item) { 
            // pour chaque elements du panier, multiplie le prix du produit par la quantité
            return $item['product']->getPrice() * $item['quantity'];
        }, $cartWithData));

        return [
            'cart' => $cartWithData,
            'total'=> $total
        ];
    }
}