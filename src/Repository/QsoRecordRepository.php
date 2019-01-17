<?php

namespace App\Repository;

use App\Entity\Log;
use App\Entity\Wwl;
use App\Entity\QsoRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method QsoRecord|null find($id, $lockMode = null, $lockVersion = null)
 * @method QsoRecord|null findOneBy(array $criteria, array $orderBy = null)
 * @method QsoRecord[]    findAll()
 * @method QsoRecord[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QsoRecordRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, QsoRecord::class);
    }

    public function getLogsNotReceived($roundDate)
    {
        $calls = $this->getEntityManager()->getRepository(Log::class)
            ->createQueryBuilder('l2')
            ->select('c.callsign')
            ->distinct('c.callsign')
            ->leftJoin('App\Entity\Callsign', 'c', 'WITH', 'l2.callsignid=c.callsignid')
            ->where(
                'c.callsign LIKE :ly',
                'l2.date = :rdate'
            )
            ->setParameter('rdate', $roundDate)
            ->setParameter('ly', 'LY%')
            ;

        return $this->createQueryBuilder('q')
            ->leftJoin('App\Entity\Log', 'l', 'WITH', 'l.logid=q.logid')
            ->distinct('q.callsign')
            ->select('q.callsign')
            ->where(
                'q.callsign LIKE :ly',
                'l.date = :rdate',
                $this->createQueryBuilder('c')->expr()->notIn('q.callsign',$calls->getDQL())
            )
            ->setParameter('rdate', $roundDate)
            ->setParameter('ly', 'LY%')
            ->getQuery()
            ->getResult()
            ;
    }

    public function getTopClaimedScores($roundid, $maxresults, $ly = true)
    {
        if ($ly) {
            $condition = 'c.callsign LIKE :ly';
        }
        else {
            $condition = 'c.callsign NOT LIKE :ly';
        }
        return $this->createQueryBuilder('q')
            ->select('c.callsign',
            '( COALESCE( SUM(
                        CASE
                        WHEN QRB(q.gridsquare, w.wwl) > 1
                        THEN QRB(q.gridsquare, w.wwl)
                        ELSE 1
                        END
                    ), 0 ) +
                COUNT(
                    DISTINCT SUBSTRING(q.gridsquare,1,4)
                    ) * 500
            ) as total_points' )
            ->leftJoin('App\Entity\Log', 'l', 'WITH', 'l.logid=q.logid')
            ->leftJoin('App\Entity\Callsign', 'c', 'WITH', 'l.callsignid=c.callsignid')
            ->leftJoin('App\Entity\Wwl', 'w', 'WITH', 'l.wwlid=w.wwlid')
            ->leftJoin('App\Entity\Round', 'r', 'WITH', 'l.date=r.date')
            ->where('r.roundid = :roundid', $condition)
            ->setParameter('roundid', $roundid)
            ->setParameter('ly', 'LY%')
            ->groupBy('l.logid')
            ->orderBy('total_points', 'DESC')
            ->setMaxResults($maxresults)
            ->getQuery()
            ->getArrayResult()
            ;
    }

    public function getRoundLogByCallsign($date,$callsign)
    {
        return $this->createQueryBuilder('q')
            ->select(
                    'q.callsign',
                    'SUBSTRING(w.wwl,1,4) as w_wwl',
                    'SUBSTRING(q.gridsquare,1,4) as q_wwl',
                    'QRB(q.gridsquare, w.wwl) as qrb'
            )
            ->leftJoin('App\Entity\Log', 'l', 'WITH', 'l.logid=q.logid')
            ->leftJoin('App\Entity\Callsign', 'c', 'WITH', 'l.callsignid=c.callsignid')
            ->leftJoin('App\Entity\Wwl', 'w', 'WITH', 'l.wwlid=w.wwlid')
            ->leftJoin('App\Entity\Round', 'r', 'WITH', 'l.date=r.date')
            ->where('r.date = :date', 'c.callsign = :callsign')
            ->setParameter('date', $date)
            ->setParameter('callsign', $callsign)
            ->orderBy('qrb', 'DESC')
            ->getQuery()
            ->getResult()
        ;
}
}
