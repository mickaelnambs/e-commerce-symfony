<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductSearch;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findVisibleQuery()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isOffer = false')
            ->orderBy('p.id', 'DESC')
        ;
    }

    public function findAllVisibleQuery(ProductSearch $search)
    {
        $query = $this->findVisibleQuery();

        if ($search->getEqualMark()) {
            $query = $query
                ->andWhere('p.mark = :equalmark')
                ->setParameter('equalmark', $search->getEqualMark());
        }

        if ($search->getEqualPrice()) {
            $query = $query
                ->andWhere('p.price = :equalprice')
                ->setParameter('equalprice', $search->getEqualPrice());
        }

        return $query->getQuery();
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
