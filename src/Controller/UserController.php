<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

#[Route('/admin')]
final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    #[IsGranted("ROLE_ADMIN")]
    public function index(UserRepository $userRepo): Response
    {

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'users' => $userRepo->findAll(), // on met directement le find all ici au lieu de réécrire la ligne au dessus du return
        ]);
    }

    // UPDATE ROLE - EDITOR / USER
    #[Route('/user/role/update/{id}', name: 'app_user_update_role')] 
    #[IsGranted("ROLE_ADMIN")]
    public function updateRole(EntityManagerInterface $entityManager, User $user): Response
    {
        $user->setRoles(["ROLE_EDITOR", "ROLE_USER"]);
        $entityManager->flush(); 

        $this->addFlash('success', 'The user\'s role have been updated to Editor !');

        return $this->redirectToRoute('app_user'); 
    }

    // // DELETE ROLE - EDITOR / USER
    // #[Route('/user/role/update/{id}', name: 'app_user_delete_role')] 
    // #[IsGranted("ROLE_ADMIN")]
    // public function updateRole(EntityManagerInterface $entityManager, User $user): Response
    // {
    //     $user->setRoles([]);
    //     $entityManager->flush(); 

    //     $this->addFlash('success', 'The user\'s role have been updated to Editor !');

    //     return $this->redirectToRoute('app_user'); 
    // }
}
