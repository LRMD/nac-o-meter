<?php

namespace App\Repository;

use App\Entity\Log;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    // /**
    //  * @return Logs[] Returns an array of Logs objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    private static function subtractOneMonth($date)
    {
        if ($date instanceof \DateTimeInterface) {
            $_d = clone $date;
            if (!($_d instanceof \DateTime)) {
                $_d = new \DateTime($date->format('Y-m-d H:i:s'));
            }
        } else {
            $_d = new \DateTime($date);
        }
        return $_d->modify('-4 weeks');
    }

    public function findLastCallsigns($maxres)
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('App\Entity\Callsign', 'c', 'WITH', 'c.callsignid=l.callsignid')
            ->select('c.callsign')
            ->orderBy('l.logid', 'DESC')
            ->setMaxResults($maxres)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findLastDate()
    {
        return $this->createQueryBuilder('l')
            ->select('MAX(l.date)')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findLastLogsByCallsign($callsign,$maxres=12)
    {
        return $this->createQueryBuilder('l')
            ->select('l.date','count(l.logid) as count','b.bandFreq as band')
            ->leftJoin('App\Entity\Callsign','c','WITH', 'c.callsignid=l.callsignid')
            ->leftJoin('App\Entity\QsoRecord','q','WITH', 'q.logid=l.logid')
            ->leftJoin('App\Entity\Band', 'b', 'WITH', 'b.bandid=l.bandid')
            ->where('c.callsign = :callsign')
            ->setParameter('callsign', $callsign)
            ->groupBy('l.logid')
            ->orderBy('l.date', 'DESC')
            ->setMaxResults($maxres)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findCallsignsByRoundDate($date)
    {
        return $this->createQueryBuilder('l')
            ->select(
                'c.callsign', 'b.band',
                'count(q.logid) as count',
                'grid2lat(w.wwl) as lat',
                'grid2lon(w.wwl) as lon',
                'SUBSTRING(w.wwl,1,4) as wwl')
            ->leftJoin('App\Entity\Callsign','c','WITH', 'c.callsignid=l.callsignid')
            ->leftJoin('App\Entity\QsoRecord','q','WITH', 'q.logid=l.logid')
            ->leftJoin('App\Entity\Wwl','w','WITH', 'w.wwlid=l.wwlid')
            ->leftJoin('App\Entity\Band','b','WITH', 'b.bandid=l.bandid')
            ->where('l.date = :date')
            ->setParameter('date', $date)
            ->groupBy('c.callsign','wwl','b.band','lat','lon')
            ->orderBy('count','DESC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findLastMonthStats($date)
    {
        return $this->createQueryBuilder('l')
            ->select('count(l.logid) as count')
            ->leftJoin('App\Entity\Band', 'b', 'WITH', 'b.bandid=l.bandid')
            ->addSelect('b.bandFreq')
            ->where('l.date > :since')
            ->setParameter('since', $this->subtractOneMonth($date))
            ->groupBy('b.bandid')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findLastMonthModeStats($date)
    {
        return $this->createQueryBuilder('l')
            ->select('count(q.qsoid) as count')
            ->leftJoin('App\Entity\QsoRecord','q','WITH', 'q.logid=l.logid')
            ->leftJoin('App\Entity\Mode', 'm', 'WITH', 'q.modeid=m.modeid')
            ->addSelect('m.mode')
            ->where('l.date > :since')
            ->setParameter('since', $this->subtractOneMonth($date))
            ->groupBy('m.modeid')
            ->getQuery()
            ->getResult()
        ;
    }
}
