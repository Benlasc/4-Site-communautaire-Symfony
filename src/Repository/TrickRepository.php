<?php

namespace App\Repository;

use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trick::class);
    }

    public function findWithAuthor($groupe)
    {
        $qb = $this->createQueryBuilder('t');

        $qb->leftJoin('t.images', 'i')
            ->addSelect('i')
            ->join('t.groupe','g')
            // ->where('t.author is not NULL')
            ->andWhere('g.name =:groupe')
            ->setParameter(':groupe', $groupe);

        return $qb->getQuery()->getResult();
    }
}
