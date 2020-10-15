<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Class StatsService.
 * 
 * @author Mickael Nambinintsoa <mickael.nambinintsoa07081999@gmail.com>
 */
class StatsService
{
    /** @var EntityManagerInterface */
    private EntityManagerInterface $entityManager;

    /**
     * StatsService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getStats()
    {
        $users      = $this->getUsersCount();
        $categories = $this->getCategoriesCount();
        $products   = $this->getProductsCount();

        return compact('users', 'categories', 'products');
    }

    public function getUsersCount()
    {
        return $this->entityManager->createQuery('SELECT COUNT(u) FROM App\Entity\User u')->getSingleScalarResult();
    }

    public function getCategoriesCount()
    {
        return $this->entityManager->createQuery('SELECT COUNT(ca) FROM App\Entity\Category ca')->getSingleScalarResult();
    }

    public function getProductsCount()
    {
        return $this->entityManager->createQuery('SELECT COUNT(p) FROM App\Entity\Product p')->getSingleScalarResult();
    }

}