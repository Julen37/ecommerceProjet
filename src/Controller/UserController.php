<?php

namespace App\Controller;

use App\Repository\UserRepository;
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

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'users' => $userRepo->findAll(), // on met directement le find all ici au lieu de réécrire la ligne au dessus du return
        ]);
    }
}
