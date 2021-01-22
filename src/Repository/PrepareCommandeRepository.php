<?php

namespace App\Repository;

use App\Entity\PrepareCommande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PrepareCommande|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrepareCommande|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrepareCommande[]    findAll()
 * @method PrepareCommande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrepareCommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrepareCommande::class);
    }

    // /**
    //  * @return PrepareCommande[] Returns an array of PrepareCommande objects
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
    public function findOneBySomeField($value): ?PrepareCommande
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
