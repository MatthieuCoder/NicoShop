<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientsController extends AbstractController
{
    #[Route('/clients', name: 'app_clients')]
    public function index(UserRepository $userRepository): Response
    {
        $u = $userRepository->findAll();
        return $this->render('clients/index.html.twig', [
            'controller_name' => 'ClientsController',
            'clients' => $u
        ]);
    }
}
