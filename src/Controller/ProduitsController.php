<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Form\CreateProductFormType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ProduitsController extends AbstractController
{
    private $security;

    public function __construct()
    {}

    #[Route('/', name: 'app_home')]
    public function index(ProduitRepository $produitRepository, RequestStack $stack): Response
    {
        $u = $produitRepository->findBy([]);
        $session = $stack->getSession();
        $basket = $session->get('basket');

        return $this->render('produits/index.html.twig', [
            'controller_name' => 'ProduitsController',
            'products' => $u,
            'basket' => $basket,
        ]);
    }

    #[Route('/produits/new', name: 'new_produit')]
    public function new(Request $request, ProduitRepository $produitRepository, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $form = $this->createForm(CreateProductFormType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        

        return $this->render('produits/new.html.twig', [
            'creationForm' => $form->createView()
        ]);
    }

    #[Route('/produits/delete/{id}', name: 'delete_produit')]
    public function delete(Request $request, ProduitRepository $produitRepository, EntityManagerInterface $entityManagerInterface, int $id) {
        $produit = $produitRepository->findOneBy(['id' => $id]);
        $produitRepository->remove($produit);
        
        $entityManagerInterface->flush();

        return $this->redirectToRoute('app_home');
    }
}
