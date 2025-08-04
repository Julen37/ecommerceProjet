<?php

namespace App\Controller;

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
}
