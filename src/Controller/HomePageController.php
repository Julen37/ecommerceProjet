<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomePageController extends AbstractController
{
    #[Route('/', name: 'app_home_page', methods: ['GET'])]
    public function homePage(CategoryRepository $categoryRepo, ProductRepository $productRepo): Response
    {
        $categories = $categoryRepo->findAll();
        $product = $productRepo->findAll();

        return $this->render('home_page/homePage.html.twig', [
            'controller_name' => 'HomePageController',
            'categories'=> $categories,
            'products'=>$product
        ]);
    }

    #[Route('/product/{id}/show', name: 'app_home_product_show', methods: ['GET'])]
    public function showProduct(Product $product, ProductRepository $productRepo): Response
    {

            $lastProductsAdd = $productRepo->findbY([],['id'=>'DESC'],5); // -> recup et affiche les 5 derniers produits par ordre decroissant
            // tableau vide signifie qu'on ne met aucun filtrage donc on recup tout les produits, 
            // l'argument desc c'est pour ranger les id par ordre decroissant, 
            // le 5 c'est pour 5resultats seulement

        return $this->render('home_page/show.html.twig', [
            'controller_name' => 'HomePageController',
            'product'=>$product,
            'products'=>$lastProductsAdd,
        ]);
    }
}
