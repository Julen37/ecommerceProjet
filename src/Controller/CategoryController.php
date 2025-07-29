<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(CategoryRepository $categoryRepo): Response
    {
        $categories = $categoryRepo->findAll();

        return $this->render('category/category.html.twig', [
            'controller_name' => 'CategoryController',
            'categories'=> $categories,
        ]);
    }

    // NEW CATEGORY
    #[Route('/categoryNew', name: 'app_category_new')]
    public function addCategory(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){ 
            $entityManager->persist($category);
            $entityManager->flush(); 

            // $this->addFlash('notice', 'Edit successfull !');

            return $this->redirectToRoute('app_category'); 
        }

        return $this->render('category/addCategory.html.twig', [
            'controller_name' => 'CategoryController',
            'form' => $form->createView(),
        ]);
    }

    // UPDATE
    #[Route('/categoryUpdate/{id}', name: 'app_category_update')] 
    public function update_form(Request $request, EntityManagerInterface $entityManager, Category $category): Response 
    {
        // $category = $entityManager->getRepository(Category::class)->find($id); // // enlever cette ligne, le $id dans la function et ajouter lentity a la place
        $form = $this->createForm(CategoryFormType::class, $category); 
        $form->handleRequest($request);
        if  ( $form->isSubmitted() && $form->isValid()){ 
            $entityManager->persist($category);
            $entityManager->flush(); 

            // $this->addFlash('notice', 'Edit successfull !');

            return $this->redirectToRoute('app_category'); 
        }

        return $this->render('category/updateCategory.html.twig', [ 
            'form' => $form->createView()
        ]);
    }

    //DELETE
    #[Route('/categoryDelete/{id}', name: 'app_category_delete')] 
    public function delete_form(EntityManagerInterface $entityManager, Category $category): Response 
    {
        
        $entityManager->remove($category); 
        $entityManager->flush(); 

        // $this->addFlash('notice', 'Deletation successfull !');

        return $this->redirectToRoute('app_category'); 
    }
}
