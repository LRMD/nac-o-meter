<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QsoRecord
 */
#[ORM\Table(name: 'qsorecords')]
#[ORM\Index(name: 'logID', columns: ['logID'])]
#[ORM\Index(name: 'callsign', columns: ['callsign'])]
#[ORM\Entity(repositoryClass: \App\Repository\QsoRecordRepository::class)]
class QsoRecord
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'qsoID', type: 'integer', nullable: false, options: ['unsigned' => true, 'comment' => 'QSO ID'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $qsoid;

    /**
     * @var int
     */
    #[ORM\Column(name: 'logID', type: 'integer', nullable: false, options: ['unsigned' => true, 'comment' => 'Attachment ID'])]
    private $logid;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'time', type: 'time', nullable: false, options: ['comment' => 'Time(4)'])]
    private $time;

    /**
     * @var bool|null
     */
    #[ORM\Column(name: 'modeID', type: 'boolean', nullable: true, options: ['comment' => 'Transmitting mode'])]
    private $modeid = '0';

    /**
     * @var string
     */
    #[ORM\Column(name: 'callsign', type: 'string', length: 16, nullable: false, options: ['comment' => 'Call(14)'])]
    private $callsign;

    /**
     * @var int
     */
    #[ORM\Column(name: 'rst_s', type: 'smallint', nullable: false, options: ['unsigned' => true, 'comment' => 'Sent-RST'])]
    private $rstS;

    /**
     * @var int
     */
    #[ORM\Column(name: 'rst_r', type: 'smallint', nullable: false, options: ['unsigned' => true, 'comment' => 'Received-RST'])]
    private $rstR;

    /**
     * @var string
     */
    #[ORM\Column(name: 'gridsquare', type: 'string', length: 6, nullable: false, options: ['comment' => 'Received WWL(6)'])]
    private $gridsquare;

    /**
     * @var bool|null
     */
    #[ORM\Column(name: 'confirm', type: 'boolean', nullable: true, options: ['comment' => 'Confirm code'])]
    private $confirm = '0';
}
