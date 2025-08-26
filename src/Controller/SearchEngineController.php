<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchEngineController extends AbstractController
{
    #[Route('/search/engine', name: 'app_search_engine')]
    public function index(Request $request, ProductRepository $productRepo): Response
    {

        if ($request->isMethod('GET')) {
            // $data = $request->request->all();
            // $word = $data['word'];
            $word = $request->get('word');

            $results = $productRepo->searchEngine($word);
        }

        return $this->render('search_engine/index.html.twig', [
            'controller_name' => 'SearchEngineController',
            'products' => $results,
            'word'=> $word,
        ]);
    }
}
