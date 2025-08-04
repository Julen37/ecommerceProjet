<?php

namespace App\Controller;

use App\Entity\AddProductHistory;
use App\Entity\Product;
use App\Form\AddProductHistoryType;
use App\Form\ProductType;
use App\Repository\AddProductHistoryRepository;
use App\Repository\ProductRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/editor/product')]
#[IsGranted("ROLE_EDITOR")]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

#region ADD
    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    { // le slugger est une interface qui va servir a transformer le nom de notre image string en slug, une chaine de charactere, "mon image de Chat" va devenir "mon-image-de-chat", transforme pour que ce soit plus sur 
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData(); // recup le fichier de l'image qui sera telechargé

            if ($image) { //si l'image existe / a été envoyé, on fait ca
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME); //on recup le nom d'origine sans les extansions (.jpeg / png..)
                $safeImageName = $slugger->slug($originalName); // on transforme/slugger le nom de l'image, pour remplacer tous les accents, espaces etc par un -
                $newFileImageName = $safeImageName.'-'.uniqid().'.'.$image->guessExtension(); //ajoute un id unique et donc l'extension

                try { // deplacer le fichier image dans le dossier qu'on a parametré dans le parametre image_directory
                    $image->move
                        ($this->getParameter('image_directory'),
                        $newFileImageName);
                } catch (FileException $exception) {
                    //gestion d'un message d'erreur si besoin
                }
                    $product->setImage($newFileImageName); // sauvegarde le nom du fichier dans son entité
            }  

            $entityManager->persist($product);
            $entityManager->flush();

            $stockHistory = new AddProductHistory();
            $stockHistory->setQuantity($product->getStock());
            $stockHistory->setProduct($product);
            $stockHistory->setCreatedAt(new DateTimeImmutable());
            $entityManager->persist($stockHistory);
            $entityManager->flush();

            $this->addFlash('success', 'The new product have been added !');

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }
#endregion ADD

#region SHOW
    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
#endregion SHOW

#region EDIT
    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'The product have been updated !');

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }
#endregion EDIT

#region DELETE
    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('danger', 'The product have been deleted.');
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
#endregion DELETE

#region ADD STOCK
    #[Route('/add/product/{id}', name: 'app_product_stock_add', methods: ['GET', 'POST'])]
    public function stockAdd($id, Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepo): Response
    {
        $stockAdd = new AddProductHistory();
        $form =$this->createForm(AddProductHistoryType::class, $stockAdd);
        $form->handleRequest($request);

        $product = $productRepo->find($id); //pour trouver les produits

        if ($form->isSubmitted() && $form->isValid()) {
            
            if($stockAdd->getQuantity()>0){ // si le stock est superieur a 0
                $newQuantity = $product->getStock() + $stockAdd->getQuantity(); // on recupere le stock deja present qu'on aditionne au stock qu'on a mit en plus
                $product->setStock($newQuantity); // et on met a jour le stock du produit

                $stockAdd->setCreatedAt(new DateTimeImmutable()); // pour mettre le createdat pour arreter l'erreur
                $stockAdd->setProduct($product); //met a jour
                $entityManager->persist($stockAdd);
                $entityManager->flush();

                $this->addFlash('success', 'The stock have been updated !');
                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);

            } else { //sinon on affiche un message flash et on redirect a la page stock add avec l'id du produit
                $this->addFlash('danger', "The stock of the product can't be less than zero.");
                return $this->redirectToRoute('app_product_stock_add', ['id'=>$product->getId()]);
            }         
        }
        return $this->render('product/addStock.html.twig',
            ['form'=> $form->createView(),
            'product'=>$product,
            ]
        );
    }
#endregion ADD STOCK

#region SHOW HISTORY
    #[Route('/add/product/{id}/stock/history', name: 'app_product_stock_add_history', methods: ['GET'])]
    public function showHistoryProductStock($id, ProductRepository $productRepo, AddProductHistoryRepository $addProductHistoryRepo): Response
    {
        $product = $productRepo->find($id);
        $productAddHistory = $addProductHistoryRepo->findBy(['product'=>$product],['id'=>'DESC']);

        return $this->render('product/addHistoryStockShow.html.twig', [
            'productsAdded' => $productAddHistory,
            'product'=>$product
        ]);
    }
#endregion SHOW HISTORY

}
