<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    #[IsGranted("ROLE_ADMIN")]
    public function index(UserRepository $userRepo): Response
    {

        return $this->render('user/user.html.twig', [
            'controller_name' => 'UserController',
            'users' => $userRepo->findAll(), // on met directement le find all ici au lieu de réécrire la ligne au dessus du return
        ]);
    }

#region UPDATE ROLE - EDITOR
    #[Route('/user/role/update/{id}', name: 'app_user_update_role')] 
    #[IsGranted("ROLE_ADMIN")]
    public function updateRoleEditor(EntityManagerInterface $entityManager, User $user): Response
    {
        $user->setRoles(["ROLE_EDITOR", "ROLE_USER"]);
        $entityManager->flush(); 

        $this->addFlash('success', 'The user\'s role have been updated to Editor !');

        return $this->redirectToRoute('app_user'); 
    }

#region DELETE ROLE - EDITOR 
    #[Route('/user/role/delete/{id}', name: 'app_user_delete_role')] 
    #[IsGranted("ROLE_ADMIN")]
    public function deleteRoleEditor(EntityManagerInterface $entityManager, User $user): Response
    {
        $user->setRoles([]);
        $entityManager->flush(); 

        $this->addFlash('success', 'The user\'s role have been updated to Editor !');

        return $this->redirectToRoute('app_user'); 
    }

#region DELETE USER
    #[Route('/user/delete/{id}', name: 'app_user_delete_user')] 
    #[IsGranted("ROLE_ADMIN")]
    public function deleteUser($id, EntityManagerInterface $entityManager, UserRepository $userRepo): Response 
    {
        $user = $userRepo->find($id);
        $entityManager->remove($user); 
        $entityManager->flush(); 

        $this->addFlash('danger', 'The user have been deleted.');

        return $this->redirectToRoute('app_user'); 
    }
}
