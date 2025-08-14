<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\Cart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    public function __construct(private readonly ProductRepository $productRepo)
    // private: accessible que depuis l'interrieur de cette class aka le statut de la fonction/ l'encaptionlation / preparer une injection de dependance
    // readonly: cette propritété va etre assigné qu'une seule fois et on pourra pas le modifier a l'exterieur on aura que le get pas le set. proteger notre code pour pas que les produit soit modifié
    {

    }

#region CART
    #[Route('/cart', name: 'app_cart', methods: ['GET'])]
    public function index(SessionInterface $session, Cart $cart): Response
    {
        $data = $cart->getcart($session);

        return $this->render('cart/cart.html.twig', [
            'items'=>$data['cart'],
            'total'=>$data['total'],
        ]);
    }
#endregion CART

#region ADD CART
    #[Route('/cart/add/{id}', name: 'app_cart_new', methods: ['GET'])]
    public function addProductToCart(int $id, SessionInterface $session): Response
    {// int c'est une declaration de type qui attend imperativement que l'id soit un entier = inting
       
        $cart = $session->get('cart',[]);
        //recup le panier actuel de la session 
        if (!empty($cart[$id])){ //si le produit est deja dans dans le panier, aka different de vide/de 0
            $cart[$id]++; // on incremente sa quantité
        }else{
            $cart[$id]=1; // sinon on l'ajoute avec une quantité de 1
        }

        $session->set('cart', $cart); // met a jour le panier dans la session
      
        return $this->redirectToRoute('app_cart');
    }
#endregion ADD CART

#region REMOVE TO CART
    #[Route('/cart/remove/{id}', name: 'app_cart_product_remove', methods: ['GET'])]
    public function removeToCart(int $id, SessionInterface $session): Response
    {
       
        $cart = $session->get('cart',[]);
        
        // if (!empty($cart[$id])){
        //     unset($cart[$id]); 
        // }
        if (!empty($cart[$id])){
            if ($cart[$id] > 1){
                $cart[$id]--;
            }else{
                unset($cart[$id]);
            }

            $session->set('cart', $cart); 
        }
        
        return $this->redirectToRoute('app_cart');
    }
#endregion REMOVE TO CART

#region DELETE CART
    #[Route('/cart/delete', name: 'app_cart_delete', methods: ['GET'])]
    public function deleteCart(SessionInterface $session): Response
    {
        $session->set('cart', []); 
        // $cart = $session->remove('cart', []);
      
        return $this->redirectToRoute('app_cart');
    }
#endregion DELETE CART
}