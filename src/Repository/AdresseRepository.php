<?php

namespace App\Repository;

use App\Entity\Adresse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Adresse|null find($id, $lockMode = null, $lockVersion = null)
 * @method Adresse|null findOneBy(array $criteria, array $orderBy = null)
 * @method Adresse[]    findAll()
 * @method Adresse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdresseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Adresse::class);
    }

    public function findByAdresse(Adresse $adresse){
        return $this->createQueryBuilder('a')
            ->andWhere('a.city =:city')
            ->setParameter('city', $adresse->getCity())
            ->andWhere('a.codePostal =:code')
            ->setParameter('code', $adresse->getCodePostal())
            ->andWhere('a.country =:country')
            ->setParameter('country', $adresse->getCountry())
            ->andWhere('a.user =:user')
            ->setParameter('user', $adresse->getUser())
            ->andWhere('a.street =:street')
            ->setParameter('street', $adresse->getStreet())
            ->getQuery()
            ->getOneOrNullResult();
    }

    // /**
    //  * @return Adresse[] Returns an array of Adresse objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Adresse
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
