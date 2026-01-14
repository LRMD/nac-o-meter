<?php

namespace App\Repository;

use App\Entity\Callsign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Callsign|null find($id, $lockMode = null, $lockVersion = null)
 * @method Callsign|null findOneBy(array $criteria, array $orderBy = null)
 * @method Callsign[]    findAll()
 * @method Callsign[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CallsignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Callsign::class);
    }

    /**
     * Get all unique callsigns that have submitted at least one log
     *
     * @return string[]
     */
    public function getAllCallsigns(): array
    {
        return $this->createQueryBuilder('c')
            ->select('DISTINCT c.callsign')
            ->innerJoin('App\Entity\Log', 'l', 'WITH', 'l.callsignid = c.callsignid')
            ->orderBy('c.callsign', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }
}
