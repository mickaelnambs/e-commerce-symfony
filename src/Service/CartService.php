<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class CartService.
 * 
 * @author Mickael Nambinintsoa <mickael.nambinintsoa07081999@gmail.com>
 */
class CartService
{
    /** @var SessionInterface */
    private SessionInterface $session;

    /** @var ProductRepository */
    private ProductRepository $productRepository;

    /**
     * CartService constructeur.
     *
     * @param SessionInterface  $session
     * @param ProductRepository $productRepository
     */
    public function __construct(SessionInterface $session, ProductRepository $productRepository)
    {
        $this->session           = $session;
        $this->productRepository = $productRepository;
    }

    /**
     * Ajouter les produits dans le panier.
     *
     * @param integer $id
     * 
     * @return void
     */
    public function add(int $id)
    {
        $panier = $this->session->get('panier', []);

        if (!empty($panier[$id])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }

        $this->session->set('panier', $panier);
    }
    
    /**
     * Enlever un produit dans le panier.
     *
     * @param integer $id
     * @return void
     */
    public function remove(int $id)
    {
        $panier = $this->session->get('panier', []);

        if (!empty($panier[$id]) && $panier[$id] > 1) {
            $panier[$id]--;
        } else {
            unset($panier[$id]);
        }

        $this->session->set('panier', $panier);
    }

    /**
     * Un tableau contenant la liste des produits dans le panier.
     *
     * @return array
     */
    public function getFullCart(): array
    {
        $panier = $this->session->get('panier', []);
        $panierWithData = [];

        foreach ($panier as $id => $quantity) {
            $panierWithData[] = [
                'product' => $this->productRepository->find($id),
                'quantity' => $quantity
            ];
        }

        return $panierWithData;
    }

    /**
     * Calcule le montant des produits ajoutés.
     *
     * @return int
     */
    public function getTotal()
    {
        $total = 0;

        foreach ($this->getFullCart() as $item) {
            $total += $item['product']->getPrice() * $item['quantity'];
        }

        return $total;
    }
}