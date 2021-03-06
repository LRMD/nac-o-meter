<?php

namespace App\Repository;

use App\Entity\Round;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Round|null find($id, $lockMode = null, $lockVersion = null)
 * @method Round|null findOneBy(array $criteria, array $orderBy = null)
 * @method Round[]    findAll()
 * @method Round[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoundRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Round::class);
    }

    public function findLastRoundDates($beforeDate) {
        return $this->createQueryBuilder('r')
            ->where('r.date <= :since')
            ->orderBy('r.date','DESC')
            ->setParameter('since', $beforeDate)
            ->setMaxResults(4)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findAllRoundYears() {
        return $this->createQueryBuilder('r')
            ->select('DISTINCT YEAR(r.date) y')
            ->addSelect('COUNT(distinct l.date) as c')
            ->leftJoin('App\Entity\Log', 'l', 'WITH', 'l.date=r.date')
            ->having('COUNT(l.date) > 0')
            ->groupBy('y')
            ->orderBy('y','DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getAllWithLogCount($year){
        return $this->createQueryBuilder('r')
            ->addSelect('COUNT(DISTINCT l.logid) as c')
            ->addSelect('COUNT(DISTINCT q.qsoid) as cq')
            ->leftJoin('App\Entity\Log', 'l', 'WITH', 'l.date=r.date')
            ->leftJoin('App\Entity\QsoRecord', 'q', 'WITH', 'q.logid=l.logid')
            ->where('YEAR(r.date) = :year')
            ->having('COUNT(l.logid) > 0')
            ->setParameter('year', $year)
            ->groupBy('r.date')
            ->orderBy('r.date', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

}
