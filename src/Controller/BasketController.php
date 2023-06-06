<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BasketController extends AbstractController
{


    #[Route('/basket/add/{id}', name: 'basket_add')]
    public function basket_add(RequestStack $stack, int $id, ProduitRepository $produitRepository): Response
    {
        $session = $stack->getSession();

        $produit = $produitRepository->findOneBy(['id' => $id]);

        if ($produit == null) {
            return $this->redirectToRoute('app_home');
        }

        $basket = $session->get('basket');

        if (isset($basket[$produit->getId()])) {
            $basket[$produit->getId()]++;
        } else {
            $basket[$produit->getId()] = 1;
        }
        $session->set('basket', $basket);

        return $this->redirectToRoute('app_home');
    }

    #[Route('/basket', name: "basket_view")]
    public function basket_view(RequestStack $stack, ProduitRepository $produitRepository)
    {
        $session = $stack->getSession();
        $basket = $session->get('basket');

        if (!isset($basket)) { $basket = []; }


        $items = [];

        foreach($basket as $k => $v) {
            $items[] = $k;
        }

        $mapping = [];
        $q = $produitRepository->findBy(array('id' => $items));
        foreach ($q as $k) {
            $mapping[$k->getId()] = $k;
        }
        
        $filtered = [];
        // Remove void
        foreach ($basket as $k => $item) {
            if (isset($mapping[$k])) {
                $filtered[$k] = $item;
            }
        }

        return $this->render('basket/basket.html.twig', [
            'basket' => $filtered,
            'items_index' => $mapping
        ]);
    }

    #[Route('/basket/remove/{id}', name: 'basket_remove')]
    public function basket_remove(RequestStack $stack, int $id) {
        $session = $stack->getSession();
        $basket = $session->get('basket');

        unset($basket[$id]);

        $session->set('basket', $basket);

        return $this->redirectToRoute('basket_view');
    }

    #[Route('/auth/basket/buy', name: 'basket_buy')]
    public function basket_buy(RequestStack $stack, EntityManagerInterface $em, ProduitRepository $produitRepository) {
        $session = $stack->getSession();
        $basket = $session->get('basket');
        if (!isset($basket)) { $basket = []; }
        if (count($basket) == 0) {
            return $this->redirectToRoute('app_home');
        }

        $user = $this->getUser();

        $command = new Commande();
        $command->setDate(new \DateTime());
        $command->setAdresseLivraison($user->getAddresse());
        $command->setCodePostalLivraison($user->getCodePostal());
        $command->setCommandes($user);

        $items = [];
        foreach($basket as $k => $v) {
            $items[] = $k;
        }
        $items = $produitRepository->findBy(array('id' => $items));
        
        foreach($items as $i) {
            $command->addProduit($i);
        }

        $em->persist($command);
        $em->flush();

        $session->set('basket', []);

        return $this->redirectToRoute('basket_view');
    }
}
